<?php

namespace FluidXml;

class CssTranslator
{
        const TAG = '(?:\w+\|)?\w+';

        // :root
        const ROOT          = '/:root\s+/';
        const ROOT_R        = '//';
        // E F
        const E_F           = '/('.self::TAG.')\s+(?=(?:[^"]*"[^"]*")*[^"]*$)('.self::TAG.')/';
        const E_F_R         = '\1//\2';
        // E > F
        const E_M_F         = '/('.self::TAG.')\s*>\s*('.self::TAG.')/';
        const E_M_F_R       = '\1/\2';
        // E + F
        const E_P_F         = '/('.self::TAG.')\s*\+\s*('.self::TAG.')/';
        const E_P_F_R       = '\1/following-sibling::*[1]/self::\2';
        // E ~ F
        const E_T_F         = '/('.self::TAG.')\s*\~\s*('.self::TAG.')/';
        const E_T_F_R       = '\1/following-sibling::*/self::\2';
        // E[attr]
        const E_ATTR        = '/('.self::TAG.')\[([\w\-]+)]/';
        const E_ATTR_R      = '\1 [ @\2 ]';
        // E[attr="attr"]
        const E_ATTR_VAL    = '/('.self::TAG.')\[([\w\-]+)\=\"(.*)\"]/';
        const E_ATTR_VAL_R  = '\1[ contains( concat( " ", normalize-space(@\2), " " ), concat( " ", "\3", " " ) ) ]';
        // E:first-child
        const FIRST_CHILD   = '/('.self::TAG.'|(\w+\|)?\*):first-child/';
        const FIRST_CHILD_R = '*[1]/self::\1';
        // #id
        const ID            = '/\#([\w\-]+)/';
        const ID_R          = "*[@id='\\1']";
        // .klass
        const KLASS         = '/(^|\s)\.([\w\-]+)+/';
        const KLASS_R       = '*[ contains( concat( " ", normalize-space(@class), " " ), concat( " ", "\2", " " ) ) ]'; // *[@class~="\2"]
        // E#id
        const E_ID          = '/('.self::TAG.')\#([\w\-]+)/';
        const E_ID_R        = "\\1[@id='\\2']";
        // E.klass
        const E_KLASS       = '/('.self::TAG.'|(\w+\|)?\*)\.([\w\-]+)+/';
        const E_KLASS_R     = '\1[ contains( concat( " ", normalize-space(@class), " " ), concat( " ", "\3", " " ) ) ]'; // \1[@class~="\2"]
        // namespace|
        const NS            = '/(\w+)\|/';
        const NS_R          = "\\1:";

        const MAP = [
                self::ROOT        => self::ROOT_R,              // must be defined before 'E F', otherwise ':root body' returns '////body'.
                self::E_F         => self::E_F_R,
                self::E_M_F       => self::E_M_F_R,
                self::FIRST_CHILD => self::FIRST_CHILD_R,
                self::E_P_F       => self::E_P_F_R,
                self::E_T_F       => self::E_T_F_R,
                self::E_ATTR      => self::E_ATTR_R,
                self::E_ATTR_VAL  => self::E_ATTR_VAL_R,
                self::KLASS       => self::KLASS_R,
                self::E_KLASS     => self::E_KLASS_R,
                self::E_ID        => self::E_ID_R,
                self::ID          => self::ID_R,
                self::NS          => self::NS_R
        ];

        private static $mapKeys;
        private static $mapValues;

        private static function mapKeys()
        {
                if (self::$mapKeys === null) {
                        self::$mapKeys = \array_keys(self::MAP);
                }

                return self::$mapKeys;
        }

        private static function mapValues()
        {
                if (self::$mapValues === null) {
                        self::$mapValues = \array_values(self::MAP);
                }

                return self::$mapValues;
        }

        public static function xpath($css)
        {
                $xpath = $css;

                $searches = self::mapKeys();
                $replaces = self::mapValues();

                // Algorithm 1:
                $xpath = \preg_replace($searches, $replaces, $xpath);

                // Algorithm 2:
                // $xpath_prev = $xpath;
                // while (true) {
                //         $xpath = \preg_replace($searches, $replaces, $xpath);
                //         if ($xpath === $xpath_prev) {
                //                 break;
                //         }
                //         $xpath_prev = $xpath;
                // }

                if ($xpath[0] !== '/') {
                        $xpath = ".//$xpath";
                }

                printf('XPATH: ' . $xpath . ' from CSS: ' . $css . "\n");
                return $xpath;
        }
}
