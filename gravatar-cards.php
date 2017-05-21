<?php
/*
Plugin Name: Awesomeness Creator
*/

function make_tag($icon, $name, $site, $site_text)
{
        return '<div class="col-md-4">
                <div class="gravatar-card-outer">
				<img class="gravatar-card-icon" src="' . $icon . '"/>
				<div class="gravatar-text-area col-md-8">
					<div class="gravatar-card-name">' . $name . '</div>
					<div class="gravatar-card-website"><a href="' . $site . '"><button class="gravatar-card-button">' . $site_text . '</button></a></div>
				</div>
				</div>
			</div>';
}

function gravatag($atts){
        wp_enqueue_style('social-cards');
        $wporg_atts = shortcode_atts(['email' => null, 'username' => null], $atts);
        $gravatar_hash = null;
        if ($wporg_atts['email'] !== null) {
                $gravatar_hash = md5($wporg_atts['email']);
        } else if ($wporg_atts['username'] !== null) {
                $gravatar_hash =  $wporg_atts['username'];
        } else {
                return "Undefined user";
        }
        $data = getGravatarData($gravatar_hash);
        $profile = $data['entry'][0];
        $name = $profile["displayName"];
        $icon = $profile["thumbnailUrl"];
        $site = count($profile["urls"]) > 0 ?  $profile["urls"][0]["value"] : $profile["profileUrl"];
        $site_text = count($profile["urls"]) > 0 ? "Website" : "Profile";
        echo "<!--" . json_encode($data) . "-->";
        return make_tag($icon, $name, $site, $site_text);
}

function keybasetag($atts){
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        wp_enqueue_style('social-cards');
        $wporg_atts = shortcode_atts(['username' => null], $atts);
        $data = getKeybaseData($wporg_atts['username']);
        $profile = $data["them"]["0"];
        $name = $profile["basics"]["username"];
        $icon = $profile["pictures"]["primary"]["url"];
        $proofs = $profile["proofs_summary"]["by_proof_type"];
        $site = "/";
        $site_text = "No Website";
        if (array_key_exists("dns", $proofs)) {
                $site = $proofs["dns"]["0"]["service_url"];
                $site_text = "Website";
        }
        echo "<!--" . json_encode($data) . "-->";
        return make_tag($icon, $name, $site, $site_text);
}

function getGravatarData($gravatar_hash)
{
        $GROUP = "GRAVATAR";
        $EXPIRES = 60 * 60 * 24; # 24 hours
        $cache_key = $GROUP . $gravatar_hash;
        $cache = get_transient($cache_key);
        if ($cache !== false) {
                return $cache;
        }
        $url = 'https://www.gravatar.com/' . $gravatar_hash . '.php';
        $data = unserialize(file_get_contents($url));
        set_transient( $cache_key, $data, $EXPIRES );
        return $data;
}

function getKeybaseData($username) {
        $GROUP = "KEYBASE";
        $EXPIRES = 60 * 60 * 24; # 24 hours
        $cache_key = $GROUP . $username;
        $cache = get_transient($cache_key);
        if ($cache !== false) {
                return $cache;
        }
        $url = 'https://keybase.io/_/api/1.0/user/lookup.json?usernames=' . $username;
        $ch = curl_init($url); // such as http://example.com/example.xml
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        set_transient( $cache_key, $data, $EXPIRES );
        return $data;
}

add_shortcode( 'gravatar', 'gravatag' );
add_shortcode( 'keybase', 'keybasetag' );
wp_register_style('social-cards', plugins_url('style.css',__FILE__ ));
?>
