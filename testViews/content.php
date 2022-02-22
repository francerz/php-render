<?php

use Francerz\Render\HTML;

HTML::layout('layout');
?>
<p>Before</p>
<?=HTML::startSection('content')?>
<p>Inner</p>
<?=HTML::endSection()?>
<p>After</p>
<?=HTML::render()?>
