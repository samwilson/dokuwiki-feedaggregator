<?php
/**
 * english language file for feedaggregator plugin
 *
 * @author Sam Wilson <sam@samwilson.id.au>
 */

// keys need to match the config setting name
$lang['force_feed'] = 'Force the given data/URL to be treated as a feed. '
        . 'This tells SimplePie to ignore the content-type provided by the server. '
        . 'Be careful when using this option, as it will also disable autodiscovery.';
$lang['force_fsockopen'] = 'Force SimplePie to use fsockopen() instead of cURL';
$lang['token'] = "A 'secret' token that will have to be supplied in the URL in order "
        . "for the aggregation script to work. For example, for a token of "
        . "<code>123abc</code> the URL must be of the form "
        . "<code>https://example.com/dokuwiki?do=feedaggregator&token=123abc</code>";
