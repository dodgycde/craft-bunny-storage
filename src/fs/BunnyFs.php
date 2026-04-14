<?php

namespace DodgyCode\BunnyStorage\fs;

use Craft;
use craft\flysystem\base\FlysystemFs;
use craft\helpers\App;
use DodgyCode\BunnyStorage\helpers\Logger;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNRegion;

/**
 * A bunny.net storage adapter for CraftCMS
 *
 * Files are stored in Bunny Storage and served over a connected Pull Zone (CDN).
 */
class BunnyFs extends FlysystemFs
{
    // -------------------------------------------------------------------------
    // Static
    // -------------------------------------------------------------------------

    public static function displayName(): string
    {
        return Craft::t('bunny-storage', 'Bunny Storage');
    }

    // -------------------------------------------------------------------------
    // Settings
    // -------------------------------------------------------------------------

    /** Storage Zone name (from bunny.net dashboard) */
    public string $storageZoneName = '';

    /** Storage Zone API password */
    public string $storagePassword = '';

    /** Storage region */
    public string $region = BunnyCDNRegion::DEFAULT;

    /**
     * Pull Zone base URL — used as the public URL for served assets
     * Leave empty to fall back to direct storage URLs (not recommended ever)
     */
    public string $cdnUrl = '';

    /** Optional subfolder within the Storage Zone */
    public string $subfolder = '';

    /**
     * Bunny.net account API key — used for CDN cache purging only
     * Optional: if missing provided, CDN cache invalidation will be skipped
     */
    public string $apiKey = '';

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['storageZoneName', 'storagePassword'], 'required'],
            [['storageZoneName', 'storagePassword', 'region', 'cdnUrl', 'subfolder', 'apiKey'], 'string'],
            [['storageZoneName', 'storagePassword', 'region', 'cdnUrl', 'subfolder', 'apiKey'], 'trim'],
        ]);
    }

    // -------------------------------------------------------------------------
    // Settings UI
    // -------------------------------------------------------------------------

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('bunny-storage/fs-settings', [
            'fs'      => $this,
            'regions' => $this->getRegionOptions(),
        ]);
    }

    // -------------------------------------------------------------------------
    // URL resolution
    // -------------------------------------------------------------------------

    /**
     * Returns the root URL for assets in this filesystem.
     * If a CDN URL is setup, files are served from there
     */
    public function getRootUrl(): ?string
    {
        if (!$this->hasUrls) {
            return null;
        }

        $baseUrl = $this->cdnUrl !== ''
            ? rtrim(App::parseEnv($this->cdnUrl), '/')
            : 'https://' . App::parseEnv($this->storageZoneName) . '.b-cdn.net';

        $subfolder = $this->getParsedSubfolder();

        return $subfolder !== ''
            ? $baseUrl . '/' . $subfolder . '/'
            : $baseUrl . '/';
    }

    // -------------------------------------------------------------------------
    // CDN invalidation
    // -------------------------------------------------------------------------

    /**
     * Purges a single file from the Bunny CDN cache.
     * Requires an account API key to be configured. Skip if not set
     */
    public function invalidateCdnPath(string $path): bool
    {
        $apiKey = App::parseEnv($this->apiKey);

        if ($apiKey === '') {
            Logger::cdnPurgeSkippedNoApiKey();
            return false;
        }

        $rootUrl = $this->getRootUrl();

        if ($rootUrl === null) {
            Logger::cdnPurgeSkippedNoRootUrl();
            return false;
        }

        $url = rtrim($rootUrl, '/') . '/' . ltrim($path, '/');
        Logger::cdnPurging($url);

        $client = Craft::createGuzzleClient();

        try {
            $response = $client->post('https://api.bunny.net/purge', [
                'query'   => ['url' => $url, 'async' => 'false'],
                'headers' => ['AccessKey' => $apiKey],
            ]);
            Logger::cdnPurgeResponse($response->getStatusCode(), $url);
        } catch (\Throwable $e) {
            Logger::cdnPurgeFailed($url, $e->getMessage());
            return false;
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Flysystem adapter
    // -------------------------------------------------------------------------

    protected function createAdapter(): FilesystemAdapter
    {
        $region = $this->region !== ''
            ? $this->region
            : (App::env('BUNNY_REGION') ?? BunnyCDNRegion::DEFAULT);

        $adapter = new BunnyCDNAdapter(
            new BunnyCDNClient(
                App::parseEnv($this->storageZoneName),
                App::parseEnv($this->storagePassword),
                $region,
            ),
            App::parseEnv($this->cdnUrl),
        );

        $subfolder = $this->getParsedSubfolder();

        return $subfolder !== ''
            ? new PathPrefixedAdapter($adapter, $subfolder)
            : $adapter;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function getParsedSubfolder(): string
    {
        static $subfolder = null;
        return $subfolder ??= trim(App::parseEnv($this->subfolder), '/');
    }

    protected function getRegionOptions(): array
    {
        return [
            ''                             => Craft::t('bunny-storage', 'Use ENV var'),
            BunnyCDNRegion::DEFAULT        => Craft::t('bunny-storage', 'Falkenstein (Default)'),
            BunnyCDNRegion::STOCKHOLM      => Craft::t('bunny-storage', 'Stockholm'),
            BunnyCDNRegion::UNITED_KINGDOM => Craft::t('bunny-storage', 'United Kingdom'),
            BunnyCDNRegion::NEW_YORK       => Craft::t('bunny-storage', 'New York'),
            BunnyCDNRegion::LOS_ANGELAS    => Craft::t('bunny-storage', 'Los Angeles'),
            BunnyCDNRegion::SINGAPORE      => Craft::t('bunny-storage', 'Singapore'),
            BunnyCDNRegion::SYDNEY         => Craft::t('bunny-storage', 'Sydney'),
            BunnyCDNRegion::JOHANNESBURG   => Craft::t('bunny-storage', 'Johannesburg'),
            BunnyCDNRegion::BRAZIL         => Craft::t('bunny-storage', 'Brazil'),
        ];
    }
}
