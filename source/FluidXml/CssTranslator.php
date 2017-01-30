<?php

namespace FluidXml;

class CssTranslator
{
        const TOKEN = '/{([[:alpha:]]+)(\d+)}/i';
        const MAP = [
                // Empty part of #id and .class
                [ '(?<=^|\s)      # The begining or an empty space.
                   (?=[.#\[])     # . | # | [',
                  '*',
                  'TAG',
                  false ],
                // #id
                [ '\#
                   ([\w\-]+)',
                  '[@id="\1"]',
                  'ID',
                  false ],
                // .class
                [ '\.
                   ([\w\-]+)',
                  '[ contains( concat(" ", normalize-space(@class), " "), concat(" ", "\1", " ") ) ]',
                  'CLASS',
                  false ],
                // [attr]
                [ '\[
                   ([\w\-]+)
                   \]',
                  '[@\1]',
                  'ATTR',
                  false ],
                // [attr="val"]
                [ '\[
                   ([\w\-]+)
                   =["\']
                   (.*)
                   ["\']
                   \]',
                  '[@\1="\2"]',
                  'ATTR',
                  false ],
                // ns|A
                [ '([\w\-]+)
                   \|
                   (?=\w)       # A namespace must be followed at least by a character.',
                  '\1:',
                  'NS',
                  false ],
                // *|A
                [ '\*           # Namespace wildcard
                   \|
                   (\w+)',
                  '*[local-name() = \'\1\']',
                  'NS',
                  false ],
                // :root
                [ ':root\b',
                  '/*',
                  'TAG',
                  false ],
                // A
                [ '(?<=^|\s|\})
                   ( [\w\-]+ | \* )',
                  '\1',
                  'TAG',
                  false ],
                // Aggregates the components of a tag in an expression.
                [ '({NS\d+})?
                   ({TAG\d+})
                   ((?:{ATTR\d+})*|)
                   ((?:{ID\d+})*|)
                   ((?:{CLASS\d+})*|)',
                  '\1\2\3\4\5',
                  'EXP',
                  false ],
                [ '({EXP\d+})
                   :first-child',
                  '*[1]/self::\1',
                  'EXP',
                  false ],
                // {} + {}
                [ '({EXP\d+})
                   \s* \+ \s*
                   ({EXP\d+})',
                  '\1/following-sibling::*[1]/self::\2',
                  'EXP',
                  true ],
                // {} ~ {}
                [ '({EXP\d+})
                   \s* \~ \s*
                   ({EXP\d+})',
                  '\1/following-sibling::*/self::\2',
                  'EXP',
                  true ],
                // {} > {}
                [ '({EXP\d+})
                   \s* > \s*
                   ({EXP\d+})',
                  '\1/\2',
                  'EXP',
                  true ],
                // {} {}
                [ '({EXP\d+})
                   \s+
                   ({EXP\d+})',
                  '\1//\2',
                  'EXP',
                  true ],
                // {}, {}
                [ '({EXP\d+})
                   \s* , \s*
                   ({EXP\d+})',
                  '\1|\2',
                  'EXP',
                  true ]
        ];

        public static function xpath($css)
        {
                $xpath = $css;

                $stack = [];
                $index = 0;

                foreach (self::MAP as $o) {
                        // The regexes have a common wrapper.
                        list($search, $replace, $id, $repeat) = $o;
                        $search = "/{$search}/xi";

                        do {
                                $prev_xpath = $xpath;
                                self::tokenize($search, $replace, $id, $xpath, $stack, $index);
                        } while ($repeat && $xpath !== $prev_xpath);
                }

                self::translateStack($stack, $xpath);

                $xpath = \trim($xpath);
                $xpath = ".//$xpath";
                $xpath = \str_replace('|', '|.//',   $xpath);
                $xpath = \str_replace('.///', '/', $xpath);

                return $xpath;
        }

        protected static function tokenize($search, $replace, $id, &$xpath, &$stack, &$index)
        {
                // The search can return 0, 1 or more fund patterns.
                $matches_count = \preg_match_all($search, $xpath, $matches, \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE);

                // \PREG_OFFSET_CAPTURE calculates offsets starting from the begining of the string $xpath.
                // Rewriting $xpath from left (first matches) creates a mismatch between the calculated offsets
                // and the actual ones. This problem can be avoided rewriting $xpath from right (last matches).
                for ($i = $matches_count - 1; $i >= 0; --$i) {
                        $match = $matches[$i];

                        // The count of the groups must exclude the entire recognized string entry.
                        $groups_count = \count($match) - 1;

                        // $match[0] is the entire recognized string.
                        // $match[>0] are the captured groups.
                        // $match[n][0] is the actual string.
                        // $match[n][1] is the position of the string.
                        list($pattern, $pattern_pos) = $match[0];

                        $xpath = \substr_replace($xpath, "{{$id}{$index}}", $pattern_pos, \strlen($pattern));

                        $groups_values = [];
                        for ($ii = 1; $ii <= $groups_count; ++$ii) {
                                // 0 is the group value, 1 is the group position.
                                $groups_values[$ii] = $match[$ii][0];
                        }

                        $stack[$index] = [$replace, $groups_values, $pattern];
                        ++$index;
                }
        }

        protected static function translateStack(&$stack, &$xpath)
        {
                do {
                        $matches_count = \preg_match_all(self::TOKEN, $xpath, $matches, \PREG_SET_ORDER);

                        for ($i = 0; $i < $matches_count; ++$i) {
                                list(, $type, $id) = $matches[$i];

                                $id = \intval($id);

                                list($replace, $groups) = $stack[$id];

                                foreach ($groups as $k => $v) {
                                        $replace = \str_replace("\\$k", $v, $replace);
                                }

                                $xpath = \str_replace("{{$type}{$id}}", $replace, $xpath);
                        }
                } while ($matches_count > 0);
        }
}
