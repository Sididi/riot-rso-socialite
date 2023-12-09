<?php

namespace SocialiteProviders\Riot;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'RIOT';

    /**
     * {@inheritdoc}
     */
    protected $scopes = [
        'openid',
        'offline_access',
        'cpid'
    ];

    /**
     * {@inherticdoc}.
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://auth.riotgames.com/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://auth.riotgames.com/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://auth.riotgames.com/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        $cpid = json_decode($response->getBody(), true)['cpid'];

        if ($cpid) {

            $response = $this->getHttpClient()->get('https://' . $cpid . '.api.riotgames.com/riot/account/v1/accounts/me', [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]);

            return json_decode($response->getBody(), true) + ['cpid' => $cpid];
        }

        return null;

    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        /* Commented fields should now be fetched from the /lol/summoner/v4/summoners/me endpoint through an additional call since Riot summoner names changes */
        return (new User())->setRaw($user)->map([
            'id'       => $user['puuid'], //$user['id'],
            'nickname' => $user['gameName'] . '#' . $user['tagLine'],
            'name'     => $user['gameName'] . '#' . $user['tagLine'],
            'email'    => null,
            'avatar'   => null, //$user['profileIconId'],

            //'accountId' => $user['accountId'],
            'puuid' => $user['puuid'],
            //'revisionDate' => $user['revisionDate'],
            //'summonerLevel' => $user['summonerLevel'],
            'cpid' => $user['cpid'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code'
        ]);
    }
}
