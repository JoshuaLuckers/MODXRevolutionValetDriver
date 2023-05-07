<?php
/**
 * Copyright (c) 2023, Joshua Lückers (https://github.com/JoshuaLuckers)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * that was distributed with this source code.
 *
 * Redistributions of files must retain the above copyright notice.
 */

namespace Valet\Drivers\Custom;

use Valet\Drivers\BasicValetDriver;

class MODXRevolutionValetDriver extends BasicValetDriver
{
    /**
     * @var string
     */
    public $basePath = '/';
    /**
     * @var string
     */
    public $requestParameter = 'q';

    /**
     * Determine if the driver serves the request.
     *
     * @param  string $sitePath
     * @param  string $siteName
     * @param  string $uri
     * @return bool
     */
    public function serves($sitePath, $siteName, $uri)
    {
        $path = dirname($uri);
        if ($path !== '/') {
            $path .= '/';
        }

        if ($this->isMODXRequest($sitePath, $path)) {
            $this->basePath = $path;
            return true;
        }

        return $this->isMODXRequest($sitePath, '/');
    }

    /**
     * Determine if the incoming request is for a static file.
     *
     * @param  string $sitePath
     * @param  string $siteName
     * @param  string $uri
     * @return string|false
     */
    public function isStaticFile($sitePath, $siteName, $uri)
    {
        $isStaticFile = parent::isStaticFile($sitePath, $siteName, $uri);

        if ($isStaticFile === false) {
            $uriParts = explode('/', ltrim($uri, '/'), 2);

            // Check if the URI contains a possible cultureKey we have to set.
            if (count($uriParts) === 2) {
                $uriCultureKeyPart = $uriParts[0];
                if (in_array($uriCultureKeyPart, $this->_getAvailableCultureKeys())) {
                    $uriPart = '/' . $uriParts[1];

                    return parent::isStaticFile($sitePath, $siteName, $uriPart);
                }
            }
        }

        return $isStaticFile;
    }

    /**
     * Get the fully resolved path to the application's front controller.
     *
     * @param  string $sitePath
     * @param  string $siteName
     * @param  string $uri
     * @return string
     */
    public function frontControllerPath($sitePath, $siteName, $uri)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestParameter = preg_replace('/' .preg_quote($this->basePath, '/'). '/', '', $uri, 1);
            $requestParameter = ltrim($requestParameter, '/');

            if ($requestParameter !== '') {
                $requestParameterParts = explode('/', $requestParameter, 2);

                // Check if the URL contains a possible cultureKey we have to set.
                if (count($requestParameterParts) === 2) {
                    $requestParameterCultureKeyPart = $requestParameterParts[0];
                    if (in_array($requestParameterCultureKeyPart, $this->_getAvailableCultureKeys())) {
                        $requestParameterQueryPart = $requestParameterParts[1];

                        $cultureKey = $requestParameterCultureKeyPart;
                        // If the requestParameter contains a cultureKey we don't need it in the query
                        $requestParameter = $requestParameterQueryPart;
                    }
                }

                if($_SERVER['REQUEST_METHOD'] === 'GET') {
                    if(isset($cultureKey)) {
                        $_GET['cultureKey'] = $cultureKey;
                    }
                    $_GET[$this->requestParameter] = $requestParameter;
                    $_REQUEST += $_GET;
                } else {
                    if(isset($cultureKey)) {
                        $_POST['cultureKey'] = $cultureKey;
                    }
                    $_POST[$this->requestParameter] = $requestParameter;
                    $_REQUEST += $_POST;
                }
            }
        }

        if ($this->basePath !== '/') {
            return parent::frontControllerPath($sitePath . $this->basePath, $siteName, preg_replace('/' .preg_quote($this->basePath, '/'). '/', '', $uri, 1));
        }

        return parent::frontControllerPath($sitePath, $siteName, $uri);
    }

    /**
     * Get the available cultureKeys MODX supports by default.
     *
     * @return array
     */
    protected function _getAvailableCultureKeys()
    {
        $cultureKeys = [
            'ar',
            'be',
            'bg',
            'cs',
            'da',
            'de',
            'el',
            'en',
            'es',
            'et',
            'fa',
            'fi',
            'fr',
            'he',
            'hi',
            'hu',
            'id',
            'it',
            'ja',
            'nl',
            'pl',
            'pt-br',
            'ro',
            'ru',
            'sv',
            'th',
            'tr',
            'uk',
            'yo',
            'zh',
        ];

        return $cultureKeys;
    }

    /**
     * @param $sitePath
     * @param $path
     * @return bool
     */
    public function isMODXRequest($sitePath, $path)
    {
        if (file_exists($sitePath . $path . 'config.core.php')) {
            return true;
        }

        if (!file_exists($sitePath . $path . 'index.php')) {
            return false;
        }

        $indexFileContents = file_get_contents($sitePath . $path . 'index.php');
        if (strpos($indexFileContents, 'modX') !== false) {
            if (strpos($indexFileContents, 'modRestService') !== false) {
                $indexFileContents = preg_replace('/\s+/', '', $indexFileContents);
                preg_match('/(?<!\/)\'requestParameter\'=>[\'"](.*)[\'"],?/U', $indexFileContents, $matches);
                if (empty($matches)) {
                    $this->requestParameter = '_rest';
                } else {
                    $this->requestParameter = array_pop($matches);
                }
            }
            return true;
        }

        return false;
    }
}
