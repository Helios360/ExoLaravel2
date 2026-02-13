<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_is_work_mail():void {
        $user = new User();
        $user->email = 'jonh@entreprise.com';

        $this->assertTrue($user->usesProfessionalEmail());
    }
    public function test_is_gmail():void{
        $user = new User;
        $user->email = 'jonh@gmail.com';
        $this->assertFalse($user->usesProfessionalEmail());
    }
}
