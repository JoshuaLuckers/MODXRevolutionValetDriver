<?php
/**
 * Copyright (c) 2018, Joshua Lückers (https://github.com/JoshuaLuckers)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * that was distributed with this source code.
 *
 * Redistributions of files must retain the above copyright notice.
 */

class MODXRevolutionValetDriver extends BasicValetDriver
{

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
        if (file_exists($sitePath . '/config.core.php')) {
            return true;
        }

        if (!file_exists($sitePath . '/index.php')) {
            return false;
        }

        if (strpos(file_get_contents($sitePath . '/index.php'), 'MODX Revolution') !== false) {
            return true;
        }

        return false;
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
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $requestParameter = ltrim($uri, '/');
            if ($requestParameter !== '') {
                $requestParameterParts = explode('/', $requestParameter, 2);

                // Check if the URL contains a possible cultureKey we have to set.
                if (count($requestParameterParts) === 2) {
                    $requestParameterCultureKeyPart = $requestParameterParts[0];
                    if (in_array($requestParameterCultureKeyPart, $this->_getAvailableCultureKeys())) {
                        $requestParameterQueryPart = $requestParameterParts[1];

                        $_GET['cultureKey'] = $requestParameterCultureKeyPart;
                        // If the requestParameter contains a cultureKey we don't need it in the query
                        $requestParameter = $requestParameterQueryPart;
                    }
                }

                $_GET['q'] = $requestParameter;
                $_REQUEST += $_GET;
            }
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
}
