<?php

if (!defined('ABSPATH')) {
    exit;
}

class PREDDIAI_Frontend_Renderer {
    const EMBED_CDN_URL = 'https://cdn.jsdelivr.net/npm/@agent-embed/js@latest/dist/web.js';

    public function register_hooks() {
        add_action('wp_footer', array($this, 'render_widget_loader'), 100);
        add_shortcode('preddiai', array($this, 'render_standard_shortcode'));
    }

    public function render_widget_loader() {
        if (!$this->can_render_on_current_request()) {
            return;
        }

        $settings = PREDDIAI_Settings::get_settings();

        if (!empty($settings['disable_widget'])) {
            return;
        }

        $snippet = isset($settings['initialization_snippet']) ? trim((string) $settings['initialization_snippet']) : '';
        if ($snippet === '') {
            return;
        }

        $request_uri = isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash((string) $_SERVER['REQUEST_URI']))
            : '/';
        if (PREDDIAI_Exclusion_Matcher::is_excluded($settings['excluded_pages'], $request_uri)) {
            return;
        }

        $user_payload = $this->get_wordpress_user_payload($settings);
        $user_payload_json = $this->encode_for_inline_script(
            $user_payload,
            array(
                'user_id' => null,
                'user_name' => null,
                'user_email' => null,
                'user_segments' => array(),
            )
        );
        $snippet_json = $this->encode_for_inline_script($snippet, '');

        ?>
<script id="preddiai-agent-loader" type="module">
import Agent from '<?php echo esc_url(self::EMBED_CDN_URL); ?>';

window.PreddiaiAgent = Agent;
window.Preddiai = window.Preddiai || {};
const preddiaiWpUser = <?php echo $user_payload_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe JSON literal for inline script. ?>;
const preddiaiContextVariables = {
  user_id: preddiaiWpUser.user_id ?? null,
  user_name: preddiaiWpUser.user_name ?? null,
  user_email: preddiaiWpUser.user_email ?? null,
  user_segments: preddiaiWpUser.user_segments ?? [],
};

window.Preddiai.user = preddiaiWpUser;
window.Preddiai.contextVariables = preddiaiContextVariables;

const preddiaiDefaultUser = {
  user_id: preddiaiWpUser.user_id ?? null,
  user_name: preddiaiWpUser.user_name ?? null,
  user_email: preddiaiWpUser.user_email ?? null,
  user_segments: preddiaiWpUser.user_segments ?? [],
};

const preddiaiDefaultContextVariables = {
  user_id: preddiaiContextVariables.user_id,
  user_name: preddiaiContextVariables.user_name,
  user_email: preddiaiContextVariables.user_email,
  user_segments: preddiaiContextVariables.user_segments,
};

const preddiaiApplyDefaultProps = (props = {}) => {
  const merged = { ...props };

  if (!merged.user) {
    merged.user = preddiaiDefaultUser;
  }

  if (!merged.contextVariables) {
    merged.contextVariables = preddiaiDefaultContextVariables;
  }

  return merged;
};

if (typeof Agent.initPopup === 'function') {
  const preddiaiOriginalInitPopup = Agent.initPopup.bind(Agent);
  Agent.initPopup = (props = {}) => preddiaiOriginalInitPopup(preddiaiApplyDefaultProps(props));
}

if (typeof Agent.initBubble === 'function') {
  const preddiaiOriginalInitBubble = Agent.initBubble.bind(Agent);
  Agent.initBubble = (props = {}) => preddiaiOriginalInitBubble(preddiaiApplyDefaultProps(props));
}

const preddiaiInitSnippet = <?php echo $snippet_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe JSON string literal for inline script. ?>;

try {
  if (preddiaiInitSnippet.trim() !== '') {
    const preddiaiRunInitSnippet = new Function('Agent', 'Preddiai', 'PreddiaiAgent', preddiaiInitSnippet);
    preddiaiRunInitSnippet(Agent, window.Preddiai, window.PreddiaiAgent);
  }
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
                'preddiai' => '',
                'agent' => '',
                'width' => '100%',
                'height' => '600px',
            ),
            is_array($atts) ? $atts : array(),
            'preddiai'
        );

        $agent_name = isset($attributes['preddiai']) && $attributes['preddiai'] !== ''
            ? $attributes['preddiai']
            : $attributes['agent'];

        $agent_name = sanitize_text_field((string) $agent_name);
        if ($agent_name === '') {
            return '';
        }

        $width = $this->sanitize_dimension((string) $attributes['width'], '100%');
        $height = $this->sanitize_dimension((string) $attributes['height'], '600px');
        $element_id = 'preddiai-standard-' . wp_unique_id();

        $settings = PREDDIAI_Settings::get_settings();
        if (!empty($settings['disable_widget'])) {
            return '';
        }

        $user_payload = $this->get_wordpress_user_payload($settings);
        $user_payload_json = $this->encode_for_inline_script(
            $user_payload,
            array(
                'user_id' => null,
                'user_name' => null,
                'user_email' => null,
                'user_segments' => array(),
            )
        );

        ob_start();
        ?>
<agent-standard id="<?php echo esc_attr($element_id); ?>" style="display:block;width:<?php echo esc_attr($width); ?>;height:<?php echo esc_attr($height); ?>;"></agent-standard>
<script type="module">
import Agent from '<?php echo esc_url(self::EMBED_CDN_URL); ?>';

const preddiaiWpUser = <?php echo $user_payload_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe JSON literal for inline script. ?>;

Agent.initStandard({
  id: <?php echo wp_json_encode($element_id); ?>,
  agentName: <?php echo wp_json_encode($agent_name); ?>,
  user: {
    user_id: preddiaiWpUser.user_id ?? null,
    user_name: preddiaiWpUser.user_name ?? null,
    user_email: preddiaiWpUser.user_email ?? null,
    user_segments: preddiaiWpUser.user_segments ?? [],
  },
  contextVariables: {
    user_id: preddiaiWpUser.user_id ?? null,
    user_name: preddiaiWpUser.user_name ?? null,
    user_email: preddiaiWpUser.user_email ?? null,
    user_segments: preddiaiWpUser.user_segments ?? [],
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

    private function encode_for_inline_script($value, $fallback_value) {
        $options = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $options |= JSON_INVALID_UTF8_SUBSTITUTE;
        }

        $encoded = wp_json_encode($value, $options);
        if ($encoded !== false) {
            return $encoded;
        }

        $fallback_encoded = wp_json_encode($fallback_value, $options);
        if ($fallback_encoded !== false) {
            return $fallback_encoded;
        }

        return 'null';
    }
}
