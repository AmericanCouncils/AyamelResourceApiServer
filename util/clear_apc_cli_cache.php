#!/usr/bin/env php
<?php

if (function_exists('apc_clear_cache')) {
	echo apc_clear_cache('user') ? "Cleared [user]".PHP_EOL : "Failed [user]".PHP_EOL;
	echo apc_clear_cache('opcode') ? "Cleared [opcode]".PHP_EOL : "Failed [opcode]".PHP_EOL;
}
