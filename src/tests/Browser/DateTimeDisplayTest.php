<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DateTimeDisplayTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
   /** @test */
    public function it_displays_datetime_in_correct_ui_format()
    {
        $this->browse(function (Browser $browser) {
           $browser->visit('/attendance') // 勤怠打刻画面のURLに変更
                   ->waitFor('#current-time') // 日時が表示される要素のID
                   ->assertSeeIn('#current-time', formatted_datetime(now()));
        });
    }
}
