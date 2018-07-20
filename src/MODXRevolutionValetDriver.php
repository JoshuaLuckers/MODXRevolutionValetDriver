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

        if (strpos(file_get_contents($sitePath . '/index.php'), 'MODX Revolution') !== false) {
            return true;
        }

        return false;
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
                $_GET['q'] = $requestParameter;
                $_REQUEST += $_GET;
            }
        }

        return parent::frontControllerPath($sitePath, $siteName, $uri);
    }
}