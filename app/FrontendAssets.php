<?php 

namespace HelloFramework;

class FrontendAssets {

	public function __construct() {

        // Don't do this on ajax requests
        if (isset($_REQUEST['ajaxify'])) return;

        add_action( 'wp_enqueue_scripts', [$this, 'addAssets']);

        add_action( 'wp_default_scripts', [$this, 'removeDefaultScript']);

        if (!CONFIG('frontend/assets/version')) {
            add_filter( 'style_loader_src', [$this, 'removeAssetVersion']);
            add_filter( 'script_loader_src', [$this, 'removeAssetVersion']);
        }

	}

    public function addAssets() {

        // Should assets have cachebusting version numbers. 
        $set_version = CONFIG('frontend/assets/version');

        // Add CSS file(s) to document head.
        $this->_addAsset('style', CONFIG('frontend/assets/css'), $set_version, 'all');

        // Add CSS file(s) for print to document head.
        $this->_addAsset('style', CONFIG('frontend/assets/css_print'), $set_version, 'print');

        // Add JS file(s) to document footer.
        $this->_addAsset('script', CONFIG('frontend/assets/js'), $set_version, true);

    }

    private function _addAsset($type, $set, $set_version, $extra = '') {

        if (!$set) return false;

        if ($type != 'script' && $type != 'style') return false;

        $function   = 'wp_enqueue_' . $type;
        $files      = (is_array($set)) ? $set : ([$set]);

        foreach ($files as $key => $file) {

            $handle     = $type . '-' . $extra . '-' . $key;
            $path       = asset($file);
            $version    = ($set_version) ? ($this->_addAssetVersion($file)) : 1;

            call_user_func_array($function, [$handle, $path, [], $version, $extra]);

        }

    }

    private function _addAssetVersion($file) {

        $path = asset($file, false, true);
        return (file_exists($path)) ? (filemtime($path)) : 404;

    }

    // ------------------------------------------------------------
    // Remove jquery-migrate, it's about time we don't need this anymore.

    public function removeDefaultScript($scripts) {

        if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
            $jquery_dependencies = $scripts->registered['jquery']->deps;
            $scripts->registered['jquery']->deps = array_diff( $jquery_dependencies, array( 'jquery-migrate' ) );
        }        

    }

    // ------------------------------------------------------------
    // Remove version number from CSS and JS embed. 

    public function removeAssetVersion($src) {

        if (strpos($src, 'ver=' )) $src = remove_query_arg( 'ver', $src );
        return $src;

    }


}