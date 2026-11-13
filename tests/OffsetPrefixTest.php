<?php

use Baspa\Timezones\Timezones;

it('keeps GMT/UTC as the default offset prefix', function () {
    expect((new Timezones)->toArray(htmlencode: false)['Asia/Tokyo'])->toBe('(GMT/UTC + 09:00) Tokyo');
});

it('uses a custom offset prefix in plain text mode', function () {
    $timezones = (new Timezones)->offsetPrefix('UTC');

    expect($timezones->toArray(htmlencode: false)['Asia/Tokyo'])->toBe('(UTC + 09:00) Tokyo')
        ->and($timezones->toArray(htmlencode: false)['America/Phoenix'])->toBe('(UTC - 07:00) Phoenix')
        ->and($timezones->toArray(grouped: true, htmlencode: false)['Asia']['Asia/Tokyo'])->toBe('(UTC + 09:00) Tokyo')
        ->and($timezones->toArrayWithContinents(htmlencode: false)['Asia/Tokyo'])->toBe('(UTC + 09:00) Asia / Tokyo');
});

it('uses a custom offset prefix in html mode', function () {
    $timezones = (new Timezones)->offsetPrefix('UTC');

    expect($timezones->toArray()['Asia/Tokyo'])->toBe('(UTC&#160;&#43;&#160;09:00)&#160;Tokyo')
        ->and($timezones->toArray()['America/Phoenix'])->toBe('(UTC&#160;&#8722;&#160;07:00)&#160;Phoenix')
        ->and($timezones->toArray(grouped: true)['Asia']['Asia/Tokyo'])->toBe('(UTC&#160;&#43;&#160;09:00)&#160;Tokyo')
        ->and($timezones->toArrayWithContinents()['Asia/Tokyo'])->toBe('(UTC&#160;&#43;&#160;09:00)&#160;Asia / Tokyo');
});

it('drops the prefix and its separator for an empty prefix in plain text mode', function () {
    $timezones = (new Timezones)->offsetPrefix('');

    expect($timezones->toArray(htmlencode: false)['Asia/Tokyo'])->toBe('(+ 09:00) Tokyo')
        ->and($timezones->toArray(htmlencode: false)['America/Phoenix'])->toBe('(- 07:00) Phoenix')
        ->and($timezones->toArray(grouped: true, htmlencode: false)['Asia']['Asia/Tokyo'])->toBe('(+ 09:00) Tokyo')
        ->and($timezones->toArrayWithContinents(htmlencode: false)['Asia/Tokyo'])->toBe('(+ 09:00) Asia / Tokyo');
});

it('drops the prefix and its separator for an empty prefix in html mode', function () {
    $timezones = (new Timezones)->offsetPrefix('');

    expect($timezones->toArray()['Asia/Tokyo'])->toBe('(&#43;&#160;09:00)&#160;Tokyo')
        ->and($timezones->toArray()['America/Phoenix'])->toBe('(&#8722;&#160;07:00)&#160;Phoenix')
        ->and($timezones->toArray(grouped: true)['Asia']['Asia/Tokyo'])->toBe('(&#43;&#160;09:00)&#160;Tokyo')
        ->and($timezones->toArrayWithContinents()['Asia/Tokyo'])->toBe('(&#43;&#160;09:00)&#160;Asia / Tokyo');
});

it('escapes the offset prefix in html mode', function () {
    $timezones = (new Timezones)->offsetPrefix('<b>"UTC"</b> & \'Z\'');

    expect($timezones->toArray()['Asia/Tokyo'])
        ->toBe('(&lt;b&gt;&quot;UTC&quot;&lt;/b&gt; &amp; &#039;Z&#039;&#160;&#43;&#160;09:00)&#160;Tokyo');
});

it('leaves the offset prefix raw in plain text mode', function () {
    $timezones = (new Timezones)->offsetPrefix('<b>"UTC"</b> & \'Z\'');

    expect($timezones->toArray(htmlencode: false)['Asia/Tokyo'])
        ->toBe('(<b>"UTC"</b> & \'Z\' + 09:00) Tokyo');
});
