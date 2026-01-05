<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * Base site URL
     */
    public string $baseURL = 'http://localhost:8080/';

    /**
     * Application Timezone
     */
    public string $appTimezone = 'UTC';

    /**
     * Force global secure requests
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * The charset used for pages.
     */
    public string $charset = 'UTF-8';

    /**
     * Default Locale
     */
    public string $defaultLocale = 'en';

    /**
     * Whether the application should negotiate for a user's locale.
     */
    public bool $negotiateLocale = false;

    /**
     * Supported locales.
     *
     * @var list<string>
     */
    public array $supportedLocales = ['en'];

    /**
     * Index file
     */
    public string $indexPage = 'index.php';

    /**
     * Session Settings
     */
    public string $sessionDriver = 'CodeIgniter\\Session\\Handlers\\DatabaseHandler';
    public string $sessionCookieName = 'ci_session';
    public int $sessionExpiration = 7200;
    public string $sessionSavePath = 'ci_sessions';
    public int $sessionMatchIP = 0;
    public bool $sessionRegenerateDestroy = false;
    public string $sessionStoreClass = '';

    /**
     * Cookie Related Variables
     */
    public string $cookiePrefix = '';
    public string $cookieDomain = '';
    public string $cookiePath = '/';
    public string $cookieSameSite = 'Lax';
    public bool $cookieSecure = false;
    public bool $cookieHTTPOnly = true;

    /**
     * CSRF Protection
     */
    public bool $CSRFProtection = true;
    public string $CSRFSameSite = 'Lax';
    public string $CSRFTokenName = 'csrf_token';
    public string $CSRFCookieName = 'csrf_cookie';
    public int $CSRFExpire = 7200;
    public bool $CSRFRegenerate = true;
    public bool $CSRFRedirect = true;
    public string $CSRFSafeRedirects = 'localhost,127.0.0.1';

    /**
     * Content Security Policy
     */
    public bool $CSPEnabled = false;

    /**
     * Proxy IPs
     */
    public array $proxyIPs = [];

    public function __construct()
    {
        parent::__construct();

        $this->baseURL = env('app.baseURL', $this->baseURL);
        $this->appTimezone = env('app.appTimezone', $this->appTimezone);
        $this->forceGlobalSecureRequests = env('app.forceGlobalSecureRequests', $this->forceGlobalSecureRequests);

        $this->sessionDriver = env('app.sessionDriver', $this->sessionDriver);
        $this->sessionSavePath = env('app.sessionSavePath', $this->sessionSavePath);
        $this->sessionExpiration = (int) env('app.sessionExpiration', $this->sessionExpiration);
        $this->sessionRegenerateDestroy = env('app.sessionRegenerateDestroy', $this->sessionRegenerateDestroy);
    }
}
