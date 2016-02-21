<?php

// Copyright (c) 2016, Daniele Orlando <fluidxml(at)danieleorlando.com>
// All rights reserved.
//
// Redistribution and use in source and binary forms, with or without modification,
// are permitted provided that the following conditions are met:
//
// 1. Redistributions of source code must retain the above copyright notice, this
//    list of conditions and the following disclaimer.
//
// 2. Redistributions in binary form must reproduce the above copyright notice,
//    this list of conditions and the following disclaimer in the documentation
//    and/or other materials provided with the distribution.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
// ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
// IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
// INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
// BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
// DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
// OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
// OF THE POSSIBILITY OF SUCH DAMAGE.

/**
 * FluidXML is a PHP library designed to manipulate XML documents with a concise
 * and fluent API. It leverages XPath and the fluent programming pattern to be
 * fun and effective.
 *
 * @author Daniele Orlando <fluidxml(at)danieleorlando.com>
 *
 * @license BSD-2-Clause
 * @license https://opensource.org/licenses/BSD-2-Clause
 */

$ds = \DIRECTORY_SEPARATOR;

// First of all.
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidInterface.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidAliasesTrait.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidSaveTrait.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}NewableTrait.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}ReservedCallStaticTrait.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}ReservedCallTrait.php";

// All.
require_once __DIR__ . "{$ds}FluidXml{$ds}CssTranslator.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidContext.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidDocument.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidHelper.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidInsertionHandler.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidNamespace.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidRepeater.php";
require_once __DIR__ . "{$ds}FluidXml{$ds}FluidXml.php";

// After of all.
require_once __DIR__ . "{$ds}FluidXml{$ds}fluid.php";
