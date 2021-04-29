<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

/** @var \Tygh\Application $application */
$application = Tygh::$app;

if (AREA == 'C') {
    $application->extend('session', function (\Tygh\Web\Session $session, \Tygh\Application $app) {
        // Visiting these dispatches will force session start
        // regardless of the HTTP request type (POST/GET/etc.)
        $force_session_start_dispatches = array(

            // A workaround for incorrect controller modes at CS-Cart.
            // These dispatches are accepting only HTTP GET requests, despite of they change server state.
            'wishlist.*',
            'product_features.add_product',
            'product_features.clear_list',
            'product_features.delete_product',
            'product_features.delete_feature',
            'product_features.compare',
        );

        $dispatch = empty($_REQUEST['dispatch']) ? 'index.index' : $_REQUEST['dispatch'];

        // Function used to match the requested dispatch against given dispatch list.
        // The main need for this is the wildcard ("*") support.
        $dispatch_matches = function ($dispatch, $dispatch_list) {
            $dispatch_parts = explode('.', $dispatch);

            foreach ($dispatch_list as $dispatch_to_compare) {
                $compared_dispatch_parts = explode('.', $dispatch_to_compare);
                $matches = false;

                foreach ($dispatch_parts as $i => $dispatch_part) {
                    if (isset($compared_dispatch_parts[$i])) {
                        $matches = (
                            $compared_dispatch_parts[$i] == $dispatch_part
                            ||
                            $compared_dispatch_parts[$i] == '*'
                        );

                        if (!$matches) {
                            break;
                        }
                    }
                }

                if ($matches) {
                    return true;
                }
            }

            return false;
        };

        $session->setSessionNamePrefix(
            'fpc_' . $session->getSessionNamePrefix()
        );

        $session->setName(ACCOUNT_TYPE);

        $session->start_on_init = false;
        $session->start_on_read = $session->requestHasSessionID();
        $session->start_on_write = $session->requestHasSessionID()
            || (fn_strtolower($_SERVER['REQUEST_METHOD']) == 'post')
            || ($dispatch_matches($dispatch, $force_session_start_dispatches));

        return $session;
    });
}