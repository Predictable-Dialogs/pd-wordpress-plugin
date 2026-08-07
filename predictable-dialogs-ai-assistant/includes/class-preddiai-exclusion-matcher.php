<?php

if (!defined('ABSPATH')) {
    exit;
}

class PREDDIAI_Exclusion_Matcher {
    public static function is_excluded($excluded_pages, $request_uri) {
        $patterns = self::parse_excluded_patterns($excluded_pages);
        if (empty($patterns)) {
            return false;
        }

        $path = wp_parse_url((string) $request_uri, PHP_URL_PATH);
        $query = wp_parse_url((string) $request_uri, PHP_URL_QUERY);

        $current_path = self::normalize_path($path);
        $current_query = self::parse_query_values($query);

        foreach ($patterns as $pattern) {
            if (self::pattern_matches_request($pattern, $current_path, $current_query)) {
                return true;
            }
        }

        return false;
    }

    private static function parse_excluded_patterns($excluded_pages) {
        $input = trim((string) $excluded_pages);
        if ($input === '') {
            return array();
        }

        return array_values(array_filter(array_map('trim', explode(',', $input)), static function ($value) {
            return $value !== '';
        }));
    }

    private static function pattern_matches_request($pattern, $current_path, $current_query) {
        $parts = explode('?', $pattern, 2);
        $path_pattern = isset($parts[0]) ? $parts[0] : '/';
        $query_pattern = isset($parts[1]) ? $parts[1] : null;

        if (!self::path_matches($path_pattern, $current_path)) {
            return false;
        }

        if ($query_pattern === null || trim($query_pattern) === '') {
            return true;
        }

        $required_query = self::parse_query_pattern($query_pattern);
        return self::query_matches($required_query, $current_query);
    }

    private static function path_matches($path_pattern, $current_path) {
        $normalized_pattern = self::normalize_pattern_path($path_pattern);
        $escaped = preg_quote($normalized_pattern, '#');
        $regex = '#^' . str_replace('\\*', '.*', $escaped) . '$#';

        return preg_match($regex, $current_path) === 1;
    }

    private static function normalize_pattern_path($path_pattern) {
        $pattern = trim((string) $path_pattern);

        if ($pattern === '') {
            return '/';
        }

        if ($pattern[0] !== '/') {
            $pattern = '/' . $pattern;
        }

        if ($pattern !== '/' && !self::ends_with($pattern, '/*') && self::ends_with($pattern, '/')) {
            $pattern = rtrim($pattern, '/');
        }

        return $pattern;
    }

    private static function normalize_path($path) {
        $normalized = (string) $path;
        if ($normalized === '') {
            $normalized = '/';
        }

        if ($normalized[0] !== '/') {
            $normalized = '/' . $normalized;
        }

        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/');
            if ($normalized === '') {
                $normalized = '/';
            }
        }

        return $normalized;
    }

    private static function parse_query_values($query_string) {
        if (!is_string($query_string) || trim($query_string) === '') {
            return array();
        }

        $query = array();
        wp_parse_str($query_string, $query);

        return $query;
    }

    private static function parse_query_pattern($query_pattern) {
        $query_pattern = trim((string) $query_pattern);
        if ($query_pattern === '') {
            return array();
        }

        $pairs = explode('&', $query_pattern);
        $result = array();

        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }

            $parts = explode('=', $pair, 2);
            $raw_key = isset($parts[0]) ? $parts[0] : '';
            $raw_value = isset($parts[1]) ? $parts[1] : '*';

            $key = rawurldecode($raw_key);
            $value = rawurldecode($raw_value);

            if ($key === '') {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    private static function query_matches($required_query, $current_query) {
        if (empty($required_query)) {
            return true;
        }

        foreach ($required_query as $key => $expected_value) {
            if (!array_key_exists($key, $current_query)) {
                return false;
            }

            $actual_value = $current_query[$key];

            if ($expected_value === '*') {
                continue;
            }

            if (is_array($actual_value)) {
                $matched = false;
                foreach ($actual_value as $value_item) {
                    if ((string) $value_item === (string) $expected_value) {
                        $matched = true;
                        break;
                    }
                }

                if (!$matched) {
                    return false;
                }

                continue;
            }

            if ((string) $actual_value !== (string) $expected_value) {
                return false;
            }
        }

        return true;
    }

    private static function ends_with($haystack, $needle) {
        $needle_length = strlen($needle);
        if ($needle_length === 0) {
            return true;
        }

        return substr($haystack, -$needle_length) === $needle;
    }
}
