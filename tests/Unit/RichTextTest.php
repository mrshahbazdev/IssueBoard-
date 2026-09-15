<?php

namespace Tests\Unit;

use Modules\IssueBoard\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    public function test_it_keeps_supported_formatting_and_removes_unsafe_markup(): void
    {
        $clean = RichText::clean(
            '<p onclick="alert(1)"><strong>Important</strong> '
            .'<a href="https://example.com" style="color:red">reference</a>'
            .'<a href="javascript:alert(1)">unsafe</a>'
            .'<script>alert(1)</script></p>'
        );

        $this->assertStringContainsString('<strong>Important</strong>', $clean);
        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('style=', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('<script', $clean);
    }

    public function test_it_preserves_plain_text_line_breaks(): void
    {
        $this->assertSame('First line<br>Second line', RichText::clean("First line\nSecond line"));
    }

    public function test_it_treats_visually_empty_markup_as_empty(): void
    {
        $this->assertNull(RichText::clean('<p><br></p>'));
    }
}
