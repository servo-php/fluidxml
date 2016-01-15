<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

class Codevelox
{
        private $cycles;
        private $tasks = [];
        private $data;

        public function __construct($cycles = 100000)
        {
                $this->cycles = $cycles;
        }

        public function data($data)
        {
                $this->data = $data;

                return $this;
        }

        protected function time()
        {
                $microtime = \microtime();

                list($usec, $sec) = \explode(' ', $microtime);

                return $sec . \substr($usec, 1);
        }

        public function add($tag, Closure $task)
        {
                $this->tasks[$tag] = $task;

                return $this;
        }

        public function run()
        {
                $results = [];

                foreach ($this->tasks as $g => $s) {
                        $start = $this->time();

                        for ($i = 0; $i < $this->cycles; ++$i) {
                                $s($this->data);
                        }

                        $end = $this->time();

                        $elapsed = $end - $start;

                        $results[$g] = $elapsed;

                        yield $g => $elapsed;
                }

                // PHP 5.6 doesn't support returning from a generator.
                // PHP 7.0 does.
                // return $results;
        }

        public function message($index, $desc, $time)
        {
                $time = \sprintf('%.2f', $time);
                return "Task {$index} took $time ($desc)\n";
        }

        public function run_and_show()
        {
                $queue = $this->run();

                $n = 1;
                foreach ($queue as $g => $i) {
                        echo $this->message($n, $g, $i);
                        ++$n;
                }

                // $return = $queue->getReturn();
                // $this->show($return);

                return $this;
        }

        public function show($results)
        {
                $n = 1;
                foreach ($results as $g => $i) {
                        echo $this->message($n, $g, $i);
                        ++$n;
                }

                return $this;
        }
}
