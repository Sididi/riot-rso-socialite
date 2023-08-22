<?php

namespace SocialiteProviders\Riot;

use SocialiteProviders\Manager\SocialiteWasCalled;

class RiotExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('riot', Provider::class);
    }
}
