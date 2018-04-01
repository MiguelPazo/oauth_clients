<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Http\Request;

class EndpointController extends Controller
{
    public function getGoogleAuth(Request $request)
    {
        $error = $request->get('error');

        if (!$error) {
            $code = $request->get('code');

            $goClient = new \Google_Client();
            $goClient->setAuthConfigFile(storage_path('app/identity/google_client_secret.json'));
            $goClient->getHttpClient()->setDefaultOption('verify', false);
            $goClient->setRedirectUri(url('/end-point/google-auth'));
            $goClient->addScope(\Google_Service_People::USERINFO_EMAIL);
            $goClient->addScope(\Google_Service_People::USERINFO_PROFILE);

            $goClient->authenticate($code);

            $request->session()->put('access_token', $goClient->getAccessToken());

            return redirect(url('/auth/google-login'));
        } else {
            return redirect()->to(url('/'));
        }
    }

    public function getFacebookAuth(Request $request)
    {
        $error = $request->get('error');

        if (!$error) {
            $stateFacebook = $request->session()->get('stateFacebook');
            $code = $request->get('code');
            $state = $request->get('state');

            if ($stateFacebook == $state) {
                $gClient = new Client();

                $gRequest = $gClient->get('https://graph.facebook.com/oauth/access_token', [
                    'query' => [
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'redirect_uri' => url('/end-point/facebook-auth'),
                        'client_id' => config('app.FACEBOOK_CLIENT_ID'),
                        'client_secret' => config('app.FACEBOOK_CLIENT_SECRET')
                    ]
                ]);

                $response = json_decode($gRequest->getBody()->getContents());
                $request->session()->put('access_token', $response->access_token);

                return redirect(url('/auth/facebook-login'));
            } else {
                return redirect()->to(url('/'));
            }
        } else {
            return redirect()->to(url('/'));
        }
    }

    public function getLinkedinAuth(Request $request)
    {
        $error = $request->get('error');

        if (!$error) {
            $stateLinkedin = $request->session()->get('stateLinkedin');
            $code = $request->get('code');
            $state = $request->get('state');

            if ($stateLinkedin == $state) {
                $gClient = new Client();

                $gRequest = $gClient->post('https://www.linkedin.com/uas/oauth2/accessToken', [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'redirect_uri' => url('/end-point/linkedin-auth'),
                        'client_id' => config('app.LINKEDIN_CLIENT_ID'),
                        'client_secret' => config('app.LINKEDIN_CLIENT_SECRET')
                    ]
                ]);

                $response = json_decode($gRequest->getBody()->getContents());
                $request->session()->put('access_token', $response->access_token);

                return redirect(url('/auth/linkedin-login'));
            } else {
                return redirect()->to(url('/'));
            }
        } else {
            return redirect()->to(url('/'));
        }
    }

    public function getTwitterAuth(Request $request)
    {
        $error = $request->get('error');

        if (!$error) {
            $authToken = $request->get('oauth_token');
            $authVerifier = $request->get('oauth_verifier');

            if ($authToken == $request->session()->get('twitterAuthToken')) {
                $oAuth = new Oauth1([
                    'consumer_key' => config('app.TWITTER_CONSUMER_KEY'),
                    'consumer_secret' => config('app.TWITTER_CONSUMER_SECRET'),
                    'token' => $authToken
                ]);

                $client = new Client();
                $client->setDefaultOption('auth', 'oauth');
                $client->getEmitter()->attach($oAuth);

                $request = $client->post('https://api.twitter.com/oauth/access_token', [
                    'body' => [
                        'oauth_verifier' => $authVerifier
                    ]
                ]);

                $oauth_token = null;
                $oauth_token_secret = null;
                $user_id = null;
                $screen_name = null;

                $response = $request->getBody()->getContents();
                parse_str($response);

                $request->session()->put('twitterToken', $oauth_token);
                $request->session()->put('twitterTokenSecret', $oauth_token_secret);
                $request->session()->put('twitterUserId', $user_id);
                $request->session()->put('twitterScreenName', $screen_name);

                return redirect(url('/auth/twitter-login'));
            } else {
                return redirect()->to(url('/'));
            }
        } else {
            return redirect()->to(url('/'));
        }
    }
}
