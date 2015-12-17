<?php

/**
 * DokuWiki Plugin feedaggregator (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Sam Wilson <sam@samwilson.id.au>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_feedaggregator extends DokuWiki_Syntax_Plugin {

    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'container';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'block';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 100;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        //$this->Lexer->addSpecialPattern('<FIXME>', $mode, 'plugin_feedaggregator');
        $this->Lexer->addEntryPattern('<feedaggregator>', $mode, 'plugin_feedaggregator');
    }

    public function postConnect() {
        $this->Lexer->addExitPattern('</feedaggregator>', 'plugin_feedaggregator');
    }

    /**
     * Handle matches of the <feedaggregator> tag, storing the list of feeds in
     * a file in the ~/data/tmp/feedaggregator directory.
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        global $conf;
        $data = array();
        // Are we to handle this match? If not, don't.
        if ($state !== DOKU_LEXER_UNMATCHED) {
            return $data;
        }

        // Get the feed URLs.
        $matchedFeeds = preg_split('/[\n\r]+/', $match, -1, PREG_SPLIT_NO_EMPTY);
        $feeds = array();
        foreach ($matchedFeeds as $feed) {
            if (filter_var($feed, FILTER_VALIDATE_URL) === false) {
                msg("Feed URL not valid: <code>$feed</code>", 2);
                continue;
            }
            $feeds[] = $feed;
        }
        $feedList = array_unique($feeds);

        // Save the feeds to a temporary CSV. It'll be ready by the action script.
        $file = fullpath($conf['tmpdir'].'/feedaggregator.csv');
        file_put_contents($file, join("\n", $feedList));

        // Get the most-recently generated HTML feed aggregation. This won't be
        // up to date with the above feeds yet, but that's okay.
        $data['html'] = io_readFile(fullpath($conf['cachedir'].'/feedaggregator/output.html'));

        return $data;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer &$renderer, $data) {
        if ($mode != 'xhtml') {
            return false;
        }
        $renderer->doc .= $data['html'];
        return true;
    }

}
