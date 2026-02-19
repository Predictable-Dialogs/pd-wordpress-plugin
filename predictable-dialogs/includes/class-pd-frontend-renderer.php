<?php

if (!defined('ABSPATH')) {
    exit;
}

class PD_WP_Frontend_Renderer {
    const EMBED_CDN_URL = 'https://cdn.jsdelivr.net/npm/@agent-embed/js@latest/dist/web.js';

    public function register_hooks() {
        add_action('wp_footer', array($this, 'render_widget_loader'), 100);
        add_shortcode('pd', array($this, 'render_standard_shortcode'));
    }

    public function render_widget_loader() {
        if (!$this->can_render_on_current_request()) {
            return;
        }

        $settings = PD_WP_Settings::get_settings();

        if (!empty($settings['disable_widget'])) {
            return;
        }

        $snippet = isset($settings['initialization_snippet']) ? trim((string) $settings['initialization_snippet']) : '';
        if ($snippet === '') {
            return;
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '/';
        if (PD_WP_Exclusion_Matcher::is_excluded($settings['excluded_pages'], $request_uri)) {
            return;
        }

        $user_payload = $this->get_wordpress_user_payload($settings);
        $user_payload_json = wp_json_encode($user_payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($user_payload_json === false) {
            $user_payload_json = '{"user_id":null,"user_name":null,"user_email":null,"user_segments":[]}';
        }

        ?>
<script id="pd-wordpress-agent-loader" type="module">
import Agent from '<?php echo esc_url(self::EMBED_CDN_URL); ?>';

window.PdAgent = Agent;
window.PdWordPress = window.PdWordPress || {};
window.PD = window.PD || {};
const pdWpUser = <?php echo $user_payload_json; ?>;
const pdContextVar = {
  user_id: pdWpUser.user_id ?? null,
  user_name: pdWpUser.user_name ?? null,
  user_email: pdWpUser.user_email ?? null,
  user_segments: pdWpUser.user_segments ?? [],
};

window.PdWordPress.user = pdWpUser;
window.PdWordPress.contextVariables = pdContextVar;
window.PD.user = pdWpUser;
window.PD.contextVariables = pdContextVar;

const pdDefaultUser = {
  user_id: pdWpUser.user_id ?? null,
  user_name: pdWpUser.user_name ?? null,
  user_email: pdWpUser.user_email ?? null,
  user_segments: pdWpUser.user_segments ?? [],
};

const pdDefaultContextVar = {
  user_id: pdContextVar.user_id,
  user_name: pdContextVar.user_name,
  user_email: pdContextVar.user_email,
  user_segments: pdContextVar.user_segments,
};

const pdApplyDefaultProps = (props = {}) => {
  const merged = { ...props };

  if (!merged.user) {
    merged.user = pdDefaultUser;
  }

  if (!merged.contextVariables) {
    merged.contextVariables = pdDefaultContextVar;
  }

  return merged;
};

if (typeof Agent.initPopup === 'function') {
  const pdOriginalInitPopup = Agent.initPopup.bind(Agent);
  Agent.initPopup = (props = {}) => pdOriginalInitPopup(pdApplyDefaultProps(props));
}

if (typeof Agent.initBubble === 'function') {
  const pdOriginalInitBubble = Agent.initBubble.bind(Agent);
  Agent.initBubble = (props = {}) => pdOriginalInitBubble(pdApplyDefaultProps(props));
}

try {
<?php echo $snippet . "\n"; ?>
} catch (error) {
  console.error('[Predictable Dialogs] Widget initialization failed.', error);
}
</script>
        <?php
    }

    private function can_render_on_current_request() {
        if (is_admin()) {
            return false;
        }

        if (wp_doing_ajax()) {
            return false;
        }

        if (is_feed() || is_trackback() || is_robots() || is_preview()) {
            return false;
        }

        return true;
    }

    public function render_standard_shortcode($atts = array()) {
        $attributes = shortcode_atts(
            array(
                'pd' => '',
                'agent' => '',
                'width' => '100%',
                'height' => '600px',
            ),
            is_array($atts) ? $atts : array(),
            'pd'
        );

        $agent_name = isset($attributes['pd']) && $attributes['pd'] !== ''
            ? $attributes['pd']
            : $attributes['agent'];

        $agent_name = sanitize_text_field((string) $agent_name);
        if ($agent_name === '') {
            return '';
        }

        $width = $this->sanitize_dimension((string) $attributes['width'], '100%');
        $height = $this->sanitize_dimension((string) $attributes['height'], '600px');
        $element_id = 'pd-standard-' . wp_unique_id();

        $settings = PD_WP_Settings::get_settings();
        if (!empty($settings['disable_widget'])) {
            return '';
        }

        $user_payload = $this->get_wordpress_user_payload($settings);
        $user_payload_json = wp_json_encode($user_payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($user_payload_json === false) {
            $user_payload_json = '{"user_id":null,"user_name":null,"user_email":null,"user_segments":[]}';
        }

        ob_start();
        ?>
<agent-standard id="<?php echo esc_attr($element_id); ?>" style="display:block;width:<?php echo esc_attr($width); ?>;height:<?php echo esc_attr($height); ?>;"></agent-standard>
<script type="module">
import Agent from '<?php echo esc_url(self::EMBED_CDN_URL); ?>';

const pdWpUser = <?php echo $user_payload_json; ?>;

Agent.initStandard({
  id: <?php echo wp_json_encode($element_id); ?>,
  agentName: <?php echo wp_json_encode($agent_name); ?>,
  user: {
    user_id: pdWpUser.user_id ?? null,
    user_name: pdWpUser.user_name ?? null,
    user_email: pdWpUser.user_email ?? null,
    user_segments: pdWpUser.user_segments ?? [],
  },
  contextVariables: {
    user_id: pdWpUser.user_id ?? null,
    user_name: pdWpUser.user_name ?? null,
    user_email: pdWpUser.user_email ?? null,
    user_segments: pdWpUser.user_segments ?? [],
  },
});
</script>
        <?php
        return trim((string) ob_get_clean());
    }

    private function sanitize_dimension($value, $default) {
        $sanitized = trim($value);
        if ($sanitized === '') {
            return $default;
        }

        if (preg_match('/^(auto|\\d+(\\.\\d+)?(px|%|rem|em|vh|vw))$/i', $sanitized)) {
            return $sanitized;
        }

        return $default;
    }

    private function get_wordpress_user_payload($settings) {
        if (empty($settings['include_logged_in_user']) || !is_user_logged_in()) {
            return array(
                'user_id' => null,
                'user_name' => null,
                'user_email' => null,
                'user_segments' => array(),
            );
        }

        $user = wp_get_current_user();

        if (!$user || !($user instanceof WP_User)) {
            return array(
                'user_id' => null,
                'user_name' => null,
                'user_email' => null,
                'user_segments' => array(),
            );
        }

        return array(
            'user_id' => (string) $user->ID,
            'user_name' => (string) $user->display_name,
            'user_email' => (string) $user->user_email,
            'user_segments' => array_values((array) $user->roles),
        );
    }
}
