<?php

namespace Rvx\Utilities\Auth;

class ClientManager
{
    protected ?object $site = null;
    public function __construct(?object $site)
    {
        $this->set($site);
    }
    public function set(?object $client)
    {
        // dd($client);
        $this->site = $client;
    }
    public function has() : bool
    {
        return $this->site !== null;
    }
    public function site() : ?object
    {
        return $this->site;
    }
    public function getUid() : string
    {
        if ($this->has()) {
            return $this->site->uid;
        }
        return '';
    }
    public function getSiteId() : int
    {
        if ($this->has()) {
            return $this->site->site_id;
        }
        return 0;
    }
    public function getName() : string
    {
        if ($this->has()) {
            return $this->site->name;
        }
        return '';
    }
    public function getDomain() : string
    {
        if ($this->has()) {
            return $this->site->domain;
        }
        return '';
    }
    public function getUrl() : string
    {
        if ($this->has()) {
            return $this->site->url;
        }
        return '';
    }
    public function getLocale() : string
    {
        if ($this->has()) {
            return $this->site->locale;
        }
        return '';
    }
    public function getEmail() : string
    {
        if ($this->has()) {
            return $this->site->email;
        }
        return '';
    }
    public function getSecret() : string
    {
        if ($this->has()) {
            return $this->site->secret;
        }
        return '';
    }
    public function getSync() : bool
    {
        if ($this->has()) {
            return (bool) $this->site->is_saas_sync;
        }
        return \false;
    }
}
