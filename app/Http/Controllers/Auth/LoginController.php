<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function getIndex(Request $request)
    {
        $loginTwitter = '#';
        $loginGoogle = '#';

        ////////////GOOGLE//////////
//        $goClient = new \Google_Client();
//        $goClient->setAuthConfigFile(storage_path('app/identity/google_client_secret.json'));
//        $goClient->getHttpClient()->setDefaultOption('verify', false);
//        $goClient->setRedirectUri(url('/end-point/google-auth'));
//        $goClient->addScope(\Google_Service_People::USERINFO_EMAIL);
//        $goClient->addScope(\Google_Service_People::USERINFO_PROFILE);
//
//        $loginGoogle = $goClient->createAuthUrl();

        ////////////FACEBOOK//////////
        $stateFacebook = md5(time());
        $params = [
            'response_type' => 'code',
            'client_id' => config('app.FACEBOOK_CLIENT_ID'),
            'redirect_uri' => url('/end-point/facebook-auth'),
            'state' => $stateFacebook,
            'scope' => 'email,user_birthday,user_about_me'
        ];

        $request->session()->put('stateFacebook', $stateFacebook);
        $loginFacebook = 'https://www.facebook.com/dialog/oauth?' . http_build_query($params);

        /////////////TWITTER///////////////
        try {
            $stack = HandlerStack::create();

            $oAuth = new Oauth1([
                'consumer_key' => config('app.TWITTER_CONSUMER_KEY'),
                'consumer_secret' => config('app.TWITTER_CONSUMER_SECRET'),
                'token' => config('app.TWITTER_TOKEN'),
                'token_secret' => config('app.TWITTER_SECRET')
            ]);

            $stack->push($oAuth);

            $gClient = new Client([
                'handler' => $stack,
                'auth' => 'oauth'
            ]);
//            $gClient->setDefaultOption('auth', 'oauth');
//            $gClient->getEmitter()->attach($oAuth);

            $gRequest = $gClient->post('https://api.twitter.com/oauth/request_token', [
                'form_params' => [
                    'oauth_callback' => url('/end-point/twitter-auth')
                ]
            ]);

            $oauth_token = null;
            $response = json_decode($gRequest->getBody()->getContents());
            parse_str($response);
            $request->session()->put('twitterAuthToken', $oauth_token);
            $loginTwitter = "https://api.twitter.com/oauth/authenticate?oauth_token=$oauth_token";
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }

        ///////////LINKEDIN////////////////
        $stateLinkedin = md5(time());
        $params = [
            'response_type' => 'code',
            'client_id' => config('app.LINKEDIN_CLIENT_ID'),
            'redirect_uri' => url('/end-point/linkedin-auth'),
            'state' => $stateLinkedin,
            'scope' => 'r_basicprofile,r_emailaddress'
        ];

        $request->session()->put('stateLinkedin', $stateLinkedin);
        $loginLinkedin = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);


        return view('login')
            ->with('loginGoogle', $loginGoogle)
            ->with('loginTwitter', $loginTwitter)
            ->with('loginLinkedin', $loginLinkedin)
            ->with('loginFacebook', $loginFacebook);
    }

    public function getGoogleLogin(Request $request)
    {
        $accesToken = $request->session()->get('access_token');

        $goClient = new \Google_Client();
        $goClient->getHttpClient()->setDefaultOption('verify', false);
        $goClient->setAuthConfigFile(storage_path('app/identity/google_client_secret.json'));
        $goClient->setAccessType('Bearer');
        $goClient->setAccessToken($accesToken);

        $goPlus = new \Google_Service_Plus($goClient);

        $data = [
            'provider' => 'google',
            'access_token' => $accesToken['access_token'],
            'info' => [
                'auth_id' => $goPlus->people->get('me')->getId(),
                'email' => $goPlus->people->get('me')->getEmails()[0]->value,
                'names' => $goPlus->people->get('me')->getName()->givenName,
                'lastnames' => $goPlus->people->get('me')->getName()->familyName,
                'image' => $goPlus->people->get('me')->getImage()->url,
            ]
        ];

        $urlReturn = $this->loginProvider($request, $data);
        return redirect($urlReturn);
    }

    public function getFacebookLogin(Request $request)
    {
        $accessToken = $request->session()->get('access_token');

        $gClient = new Client();
        $gRequest = $gClient->get('https://graph.facebook.com/me', [
            'query' => [
                'fields' => 'id,first_name,last_name,email,gender,birthday,locale,timezone,picture',
                'access_token' => $accessToken
            ]
        ]);

        $data = [
            'provider' => 'facebook',
            'access_token' => $accessToken,
            'info' => json_decode($gRequest->getBody()->getContents(), true)
        ];

        $urlReturn = $this->loginProvider($request, $data);

        return redirect($urlReturn);
    }

    public function getTwitterLogin(Request $request)
    {
        $key = $request->session()->get('twitterToken');
        $keySecret = $request->session()->get('twitterTokenSecret');
        $userId = $request->session()->get('twitterUserId');

        $oAuth = new Oauth1([
            'consumer_key' => config('app.TWITTER_CONSUMER_KEY'),
            'consumer_secret' => config('app.TWITTER_CONSUMER_SECRET'),
            'token' => $key,
            'token_secret' => $keySecret
        ]);

        $gClient = new Client();
        $gClient->setDefaultOption('auth', 'oauth');
        $gClient->getEmitter()->attach($oAuth);

        $gRequest = $gClient->get('https://api.twitter.com/1.1/users/show.json', [
            'query' => [
                'id' => $userId,
                'include_entities' => true
            ]
        ]);

        $data = [
            'provider' => 'twitter',
            'info' => json_decode($gRequest->getBody()->getContents(), true)
        ];

        $urlReturn = $this->loginProvider($request, $data);
        return redirect($urlReturn);
    }

    public function getLinkedinLogin(Request $request)
    {
        $accessToken = $request->session()->get('access_token');

        $gClient = new Client();
        $data = [
            'oauth2_access_token' => $accessToken,
            'format' => 'json'
        ];

        $stringInfo = 'id,first-name,last-name,picture-url,email-address';
        $gRequest = $gClient->get("https://api.linkedin.com/v1/people/~:($stringInfo)?" . http_build_query($data));

        $data = [
            'provider' => 'linkedin',
            'access_token' => $accessToken,
            'info' => json_decode($gRequest->getBody()->getContents(), true)
        ];

        $urlReturn = $this->loginProvider($request, $data);
        return redirect($urlReturn);
    }

    public function loginProvider($request, $data)
    {
        Session::flush();

        $request->session()->put('data', $data);

        return url('/info');
    }

    public function getLogout()
    {
        Session::flush();

        return redirect()->to('/');
    }
}
