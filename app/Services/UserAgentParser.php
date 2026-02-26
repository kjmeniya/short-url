<?php

namespace App\Services;

class UserAgentParser
{
    protected string $userAgent;

    public function __construct(?string $userAgent = null)
    {
        $this->userAgent = $userAgent ?? '';
    }

    /**
     * Set the user agent string.
     */
    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    /**
     * Get the browser name and version.
     */
    public function browser(): string
    {
        $browsers = [
            'Chrome' => '/Chrome\/([0-9.]+)/',
            'Firefox' => '/Firefox\/([0-9.]+)/',
            'Safari' => '/Version\/([0-9.]+).*Safari/',
            'Edge' => '/Edg\/([0-9.]+)/',
            'Opera' => '/Opera\/([0-9.]+)/',
            'Internet Explorer' => '/MSIE ([0-9.]+)/',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $this->userAgent, $matches)) {
                return $browser . (isset($matches[1]) ? ' ' . $matches[1] : '');
            }
        }

        return 'Unknown';
    }

    /**
     * Get the platform/operating system.
     */
    public function platform(): string
    {
        $platforms = [
            'Windows NT 10.0' => 'Windows 10',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 6.2' => 'Windows 8',
            'Windows NT 6.1' => 'Windows 7',
            'Windows NT 6.0' => 'Windows Vista',
            'Windows NT 5.1' => 'Windows XP',
            'Windows NT 5.0' => 'Windows 2000',
            'Mac OS X' => 'macOS',
            'iPhone OS' => 'iOS',
            'iPad' => 'iPadOS',
            'Android' => 'Android',
            'Linux' => 'Linux',
            'Ubuntu' => 'Ubuntu',
            'FreeBSD' => 'FreeBSD',
            'OpenBSD' => 'OpenBSD',
            'NetBSD' => 'NetBSD',
            'SunOS' => 'Solaris',
        ];

        foreach ($platforms as $pattern => $platform) {
            if (stripos($this->userAgent, $pattern) !== false) {
                // For Android, try to get version
                if ($pattern === 'Android' && preg_match('/Android ([0-9.]+)/', $this->userAgent, $matches)) {
                    return 'Android ' . $matches[1];
                }
                // For iOS, try to get version
                if ($pattern === 'iPhone OS' && preg_match('/iPhone OS ([0-9_]+)/', $this->userAgent, $matches)) {
                    return 'iOS ' . str_replace('_', '.', $matches[1]);
                }
                // For macOS, try to get version
                if ($pattern === 'Mac OS X' && preg_match('/Mac OS X ([0-9_]+)/', $this->userAgent, $matches)) {
                    return 'macOS ' . str_replace('_', '.', $matches[1]);
                }
                return $platform;
            }
        }

        return 'Unknown';
    }

    /**
     * Check if the device is mobile.
     */
    public function isMobile(): bool
    {
        // Check for tablet patterns first - if it's a tablet, it's not mobile
        $tabletPatterns = [
            'iPad',
            'Android.*Tablet',
            'Tablet',
            'Kindle',
            'PlayBook',
            'Nexus 7',
            'Nexus 10',
            'Galaxy Tab',
        ];

        foreach ($tabletPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $this->userAgent)) {
                return false;
            }
        }

        $mobilePatterns = [
            'Mobile',
            'Android',
            'iPhone',
            'iPod',
            'BlackBerry',
            'Windows Phone',
            'webOS',
            'Opera Mini',
            'IEMobile',
            'Mobile Safari',
        ];

        foreach ($mobilePatterns as $pattern) {
            if (stripos($this->userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the device is a tablet.
     */
    public function isTablet(): bool
    {
        $tabletPatterns = [
            'iPad',
            'Android.*Tablet',
            'Tablet',
            'Kindle',
            'PlayBook',
            'Nexus 7',
            'Nexus 10',
            'Galaxy Tab',
        ];

        foreach ($tabletPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $this->userAgent)) {
                return true;
            }
        }

        // Android tablets usually don't have "Mobile" in user agent
        if (stripos($this->userAgent, 'Android') !== false && 
            stripos($this->userAgent, 'Mobile') === false) {
            return true;
        }

        return false;
    }

    /**
     * Check if the device is desktop.
     */
    public function isDesktop(): bool
    {
        // If user agent is empty or too short, it's unknown
        if (empty($this->userAgent) || strlen($this->userAgent) < 10) {
            return false;
        }

        return !$this->isMobile() && !$this->isTablet();
    }

    /**
     * Get device type as string.
     */
    public function getDeviceType(): string
    {
        if ($this->isMobile()) {
            return 'Mobile';
        } elseif ($this->isTablet()) {
            return 'Tablet';
        } elseif ($this->isDesktop()) {
            return 'Desktop';
        }

        return 'Unknown';
    }

    /**
     * Check if the user agent is a bot/crawler.
     */
    public function isBot(): bool
    {
        $botPatterns = [
            'bot',
            'crawler',
            'spider',
            'scraper',
            'Googlebot',
            'Bingbot',
            'Slurp',
            'DuckDuckBot',
            'Baiduspider',
            'YandexBot',
            'facebookexternalhit',
            'Twitterbot',
            'LinkedInBot',
            'WhatsApp',
            'Telegram',
        ];

        foreach ($botPatterns as $pattern) {
            if (stripos($this->userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get detailed information about the user agent.
     */
    public function getDetails(): array
    {
        return [
            'user_agent' => $this->userAgent,
            'browser' => $this->browser(),
            'platform' => $this->platform(),
            'device_type' => $this->getDeviceType(),
            'is_mobile' => $this->isMobile(),
            'is_tablet' => $this->isTablet(),
            'is_desktop' => $this->isDesktop(),
            'is_bot' => $this->isBot(),
        ];
    }
}
