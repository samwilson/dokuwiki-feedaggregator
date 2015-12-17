<?php

/**
 * DokuWiki Plugin feedaggregator (Action Component)
 *
 * @license GPL 3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author  Sam Wilson <sam@samwilson.id.au>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_feedaggregator extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for the PREPROCESS event.
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle');
    }

    /**
     * 
     *
     * @param Doku_Event $event  Not used
     * @param mixed      $param  Not used
     * @return void
     */
    public function handle(Doku_Event &$event, $param) {
        global $conf;
        if ($event->data != 'feedaggregator') {
            return;
        }
        $event->preventDefault();

        // Get the feed list.
        $feeds = file(fullpath($conf['tmpdir'].'/feedaggregator.csv'));

        // Set up SimplePie and merge all the feeds together.
        $simplepie = new FeedParser();
        $ua = 'Mozilla/4.0 (compatible; DokuWiki feedaggregator plugin '.wl('', '', true).')';
        $simplepie->set_useragent($ua);
        $simplepie->set_feed_url($feeds);

        // Set up caching.
        $cacheDir = fullpath($conf['cachedir'].'/feedaggregator');
        io_mkdir_p($cacheDir);
        $simplepie->enable_cache();
        $simplepie->set_cache_location($cacheDir);

        // Run the actual feed aggregation.
        $simplepie->init();

        // Check for errors.
        if ($simplepie->error()) {
            header("Content-type:text/plain");
            echo join("\n", $simplepie->error());
        }

        // Create the output HTML and cache it for use by the syntax component.
        $html = '';
        foreach ($simplepie->get_items() as $item) {
            $html .= "<div class='feedaggregator_item'>\n"
                    ."<h2>".$item->get_title()."</h2>\n"
                    .$item->get_content()."\n"
                    ."<p>"
                    ."  <a href='".$item->get_permalink()."'>Published ".$item->get_date('j M Y')."</a> "
                    ."  in <a href='".$item->get_feed()->get_permalink()."'>".$item->get_feed()->get_title()."</a>"
                    ."</p>\n"
                    ."</div>\n\n";
        }
        io_saveFile($cacheDir.'/output.html', $html);

        // Output nothing, as this should be run from cron and we don't want to
        // flood the logs with success.
        exit(0);
    }

}
