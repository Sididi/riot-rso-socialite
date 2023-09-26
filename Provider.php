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

            $response = $this->getHttpClient()->get('https://' . $cpid . '.api.riotgames.com/lol/summoner/v4/summoners/me', [
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
        return (new User())->setRaw($user)->map([
            'id'       => $user['id'],
            'nickname' => $user['name'],
            'name'     => null,
            'email'    => null,
            'avatar'   => $user['profileIconId'], // TODO: get avatar from riot api,

            'accountId' => $user['accountId'],
            'puuid' => $user['puuid'],
            'revisionDate' => $user['revisionDate'],
            'summonerLevel' => $user['summonerLevel'],
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
