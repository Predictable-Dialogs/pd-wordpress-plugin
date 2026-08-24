<?php

if (!defined('ABSPATH')) {
    exit;
}

class PREDDIAI_Frontend_Renderer {
    const EMBED_CDN_URL = 'https://cdn.jsdelivr.net/npm/@agent-embed/js@latest/dist/web.js';

    /**
     * @var bool
     */
    private $standard_widget_rendered = false;

    public function register_hooks() {
        add_action('wp_footer', array($this, 'render_widget_loader'), 100);
        add_filter('the_content', array($this, 'append_standard_widget_to_page_content'));
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

        $raw_snippet = $this->resolve_initialization_snippet($settings);
        $widget_type = PREDDIAI_Settings::detect_widget_type_from_snippet($raw_snippet);
        if ($widget_type === '') {
            $widget_type = isset($settings['widget_type']) ? (string) $settings['widget_type'] : 'Standard';
        }

        if ($widget_type === 'Standard') {
            $this->render_standard_home_fallback($settings, $raw_snippet);
            return;
        }

        $snippet = PREDDIAI_Settings::normalize_initialization_snippet_for_execution($raw_snippet);
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

    public function append_standard_widget_to_page_content($content) {
        if (!$this->can_render_on_current_request()) {
            return $content;
        }

        if (!is_singular('page') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        if (has_shortcode($content, 'preddiai')) {
            return $content;
        }

        $settings = PREDDIAI_Settings::get_settings();
        if (!empty($settings['disable_widget'])) {
            return $content;
        }

        $raw_snippet = $this->resolve_initialization_snippet($settings);
        $widget_type = PREDDIAI_Settings::detect_widget_type_from_snippet($raw_snippet);
        if ($widget_type === '') {
            $widget_type = isset($settings['widget_type']) ? (string) $settings['widget_type'] : 'Standard';
        }

        if ($widget_type !== 'Standard') {
            return $content;
        }

        if (PREDDIAI_Settings::normalize_initialization_snippet_for_execution($raw_snippet) === '') {
            return $content;
        }

        $mode = isset($settings['standard_placement_mode']) ? (string) $settings['standard_placement_mode'] : 'all_pages';
        if ($mode === 'manual') {
            return $content;
        }

        if ($mode === 'selected_page') {
            $selected_page_id = isset($settings['standard_page_id']) ? absint($settings['standard_page_id']) : 0;
            if ($selected_page_id <= 0 || absint(get_queried_object_id()) !== $selected_page_id) {
                return $content;
            }
        } elseif ($mode !== 'all_pages') {
            return $content;
        }

        $request_uri = isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash((string) $_SERVER['REQUEST_URI']))
            : '/';
        if (PREDDIAI_Exclusion_Matcher::is_excluded($settings['excluded_pages'], $request_uri)) {
            return $content;
        }

        $standard_widget = $this->render_standard_widget_from_snippet($settings, $raw_snippet);

        if ($standard_widget === '') {
            return $content;
        }

        $content_position = isset($settings['standard_content_position']) ? (string) $settings['standard_content_position'] : 'below_content';
        if ($content_position === 'above_content') {
            return $standard_widget . "\n\n" . $content;
        }

        return $content . "\n\n" . $standard_widget;
    }

    private function render_standard_home_fallback($settings, $raw_snippet) {
        if ($this->standard_widget_rendered) {
            return;
        }

        $mode = isset($settings['standard_placement_mode']) ? (string) $settings['standard_placement_mode'] : 'all_pages';
        if ($mode !== 'all_pages') {
            return;
        }

        if (!is_home() && !is_front_page()) {
            return;
        }

        $request_uri = isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash((string) $_SERVER['REQUEST_URI']))
            : '/';
        if (PREDDIAI_Exclusion_Matcher::is_excluded($settings['excluded_pages'], $request_uri)) {
            return;
        }

        $standard_widget = $this->render_standard_widget_from_snippet($settings, $raw_snippet);

        if ($standard_widget === '') {
            return;
        }

        echo "\n" . $standard_widget . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_standard_widget_from_snippet returns sanitized widget markup.
    }

    private function resolve_initialization_snippet($settings) {
        $saved_snippet = isset($settings['initialization_snippet']) ? trim((string) $settings['initialization_snippet']) : '';
        if ($saved_snippet !== '') {
            return $saved_snippet;
        }

        $agent_name = isset($settings['agent_name']) ? trim((string) $settings['agent_name']) : '';
        if ($agent_name !== '') {
            $widget_type = isset($settings['widget_type']) ? (string) $settings['widget_type'] : 'Standard';
            if ($widget_type === 'Popup') {
                return $this->wrap_module_snippet($this->build_popup_snippet($agent_name, $settings));
            }

            if ($widget_type === 'Bubble') {
                return $this->wrap_module_snippet($this->build_bubble_snippet($agent_name, $settings));
            }

            if ($widget_type === 'Standard') {
                return $this->build_standard_snippet($agent_name, $settings);
            }
        }

        return '';
    }

    private function wrap_module_snippet($snippet) {
        return "<script type=\"module\">\n"
            . "  import Agent from '" . self::EMBED_CDN_URL . "';\n"
            . trim((string) $snippet) . "\n"
            . '</script>';
    }

    private function build_standard_snippet($agent_name, $settings) {
        $width = $this->sanitize_dimension($this->setting_string($settings, 'standard_width', '100%'), '100%');
        $height = $this->sanitize_dimension($this->setting_string($settings, 'standard_height', '600px'), '600px');

        return $this->wrap_module_snippet(
            "Agent.initStandard({\n  agentName: " . $this->js_literal($agent_name) . ",\n});"
        ) . "\n<agent-standard style=\"width: " . esc_attr($width) . '; height: ' . esc_attr($height) . ";\"></agent-standard>";
    }

    private function build_popup_snippet($agent_name, $settings) {
        $fields = array(
            'agentName: ' . $this->js_literal($agent_name),
        );

        if (!empty($settings['popup_auto_show_enabled'])) {
            $delay_seconds = $this->positive_int($settings['popup_auto_show_delay_seconds'], 3);
            $fields[] = 'autoShowDelay: ' . ($delay_seconds * 1000);
        }

        return "Agent.initPopup({\n  " . implode(",\n  ", $fields) . ",\n});";
    }

    private function build_bubble_snippet($agent_name, $settings) {
        $fields = array(
            'agentName: ' . $this->js_literal($agent_name),
        );

        if (!empty($settings['bubble_preview_message_enabled'])) {
            $preview_fields = array(
                'message: ' . $this->js_literal($this->setting_string($settings, 'bubble_preview_message_text', 'Need help? Tap here to chat with us!')),
            );

            $avatar_url = $this->setting_string($settings, 'bubble_preview_message_avatar_url', '');
            if ($avatar_url !== '') {
                $preview_fields[] = 'avatarUrl: ' . $this->js_literal($avatar_url);
            }

            if (!empty($settings['bubble_preview_auto_show_enabled'])) {
                $delay_seconds = $this->positive_int($settings['bubble_preview_auto_show_delay_seconds'], 3);
                $preview_fields[] = 'autoShowDelay: ' . ($delay_seconds * 1000);
            }

            $fields[] = "previewMessage: {\n    " . implode(",\n    ", $preview_fields) . ",\n  }";
        }

        $placement = $this->setting_string($settings, 'bubble_placement', 'right') === 'left' ? 'left' : 'right';
        $button_size = $this->setting_string($settings, 'bubble_button_size', 'medium') === 'large' ? 'large' : 'medium';
        $button_color = $this->setting_string($settings, 'bubble_button_color', '#2b3e13');
        $custom_icon_src = $this->setting_string($settings, 'bubble_custom_icon_src', '');
        $button_fields = array(
            'size: ' . $this->js_literal($button_size),
            'backgroundColor: ' . $this->js_literal($button_color),
        );

        if ($custom_icon_src !== '') {
            $button_fields[] = 'customIconSrc: ' . $this->js_literal($custom_icon_src);
        }

        $fields[] = "theme: {\n    placement: " . $this->js_literal($placement) . ",\n    button: {\n      " . implode(",\n      ", $button_fields) . ",\n    },\n  }";

        return "Agent.initBubble({\n  " . implode(",\n  ", $fields) . ",\n});";
    }

    private function setting_string($settings, $key, $default) {
        if (!isset($settings[$key])) {
            return $default;
        }

        $value = trim((string) $settings[$key]);
        return $value !== '' ? $value : $default;
    }

    private function positive_int($value, $default) {
        $number = absint($value);
        return $number > 0 ? $number : $default;
    }

    private function js_literal($value) {
        return $this->encode_for_inline_script($value, '');
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

    private function render_standard_widget_from_snippet($settings, $raw_snippet) {
        $snippet = PREDDIAI_Settings::normalize_initialization_snippet_for_execution($raw_snippet);
        if ($snippet === '') {
            return '';
        }

        $dimensions = PREDDIAI_Settings::extract_standard_dimensions_from_snippet($raw_snippet);
        $width = $dimensions['width'] !== ''
            ? $dimensions['width']
            : $this->sanitize_dimension($this->setting_string($settings, 'standard_width', '100%'), '100%');
        $height = $dimensions['height'] !== ''
            ? $dimensions['height']
            : $this->sanitize_dimension($this->setting_string($settings, 'standard_height', '600px'), '600px');
        $element_id = 'preddiai-standard-' . wp_unique_id();

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

        ob_start();
        ?>
<agent-standard id="<?php echo esc_attr($element_id); ?>" style="display:block;width:<?php echo esc_attr($width); ?>;height:<?php echo esc_attr($height); ?>;margin-left:auto;margin-right:auto;"></agent-standard>
<script type="module">
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

const preddiaiStandardElementId = <?php echo wp_json_encode($element_id); ?>;
const preddiaiDefaultUser = {
  user_id: preddiaiWpUser.user_id ?? null,
  user_name: preddiaiWpUser.user_name ?? null,
  user_email: preddiaiWpUser.user_email ?? null,
  user_segments: preddiaiWpUser.user_segments ?? [],
};

const preddiaiApplyStandardProps = (props = {}) => {
  const merged = { ...props, id: preddiaiStandardElementId };

  if (!merged.user) {
    merged.user = preddiaiDefaultUser;
  }

  if (!merged.contextVariables) {
    merged.contextVariables = preddiaiContextVariables;
  }

  return merged;
};

let preddiaiOriginalInitStandard = null;
if (typeof Agent.initStandard === 'function') {
  preddiaiOriginalInitStandard = Agent.initStandard;
  const preddiaiBoundInitStandard = preddiaiOriginalInitStandard.bind(Agent);
  Agent.initStandard = (props = {}) => preddiaiBoundInitStandard(preddiaiApplyStandardProps(props));
}

const preddiaiInitSnippet = <?php echo $snippet_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe JSON string literal for inline script. ?>;

try {
  if (preddiaiInitSnippet.trim() !== '') {
    const preddiaiRunInitSnippet = new Function('Agent', 'Preddiai', 'PreddiaiAgent', preddiaiInitSnippet);
    preddiaiRunInitSnippet(Agent, window.Preddiai, window.PreddiaiAgent);
  }
} catch (error) {
  console.error('[Predictable Dialogs] Standard widget initialization failed.', error);
} finally {
  if (preddiaiOriginalInitStandard) {
    Agent.initStandard = preddiaiOriginalInitStandard;
  }
}
</script>
        <?php
        $this->standard_widget_rendered = true;
        return trim((string) ob_get_clean());
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
        $this->standard_widget_rendered = true;
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
