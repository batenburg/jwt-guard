<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Test\Unit\Guards;

use Batenburg\JWTGuard\Exceptions\MethodNotImplementedException;
use Batenburg\JWTGuard\Guards\JWTGuard;
use Batenburg\JWTVerifier\JWT\DataSet;
use Batenburg\JWTVerifier\JWT\JWT;
use Batenburg\JWTVerifier\JWTVerifier\Exceptions\JWTVerifierException;
use Batenburg\JWTVerifier\JWTVerifier\JWTVerifier;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Batenburg\JWTGuard\Guards\JWTGuard
 */
class JwtGuardTest extends TestCase
{
    /**
     * @var MockObject|UserProvider
     */
    private $userProvider;

    /**
     * @var MockObject|JWTVerifier
     */
    private $jwtVerifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userProvider = $this->createMock(UserProvider::class);
        $this->jwtVerifier = $this->createMock(JWTVerifier::class);
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::check
     * @throws AuthenticationException
     */
    public function testCheck(): void
    {
        $token = 'jwt';
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->willReturn(new JWT($token, new DataSet([]), new DataSet(['sub' => '1'])));
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);

        $result = $jwtGuard->check();

        $this->assertTrue($result);
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::check
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function testCheckWithFailingAuthentication(): void
    {
        $this->expectException(AuthenticationException::class);

        $token = 'jwt';
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->will($this->throwException(new JWTVerifierException));
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);

        $jwtGuard->check();
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::guest
     * @throws AuthenticationException
     */
    public function testGuest(): void
    {
        $token = 'jwt';
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->willReturn(new JWT($token, new DataSet([]), new DataSet(['sub' => '1'])));
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);

        $result = $jwtGuard->guest();

        $this->assertFalse($result);
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::guest
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function testGuestWithFailingAuthentication(): void
    {
        $this->expectException(AuthenticationException::class);

        $token = 'jwt';
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->will($this->throwException(new JWTVerifierException));
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);

        $jwtGuard->guest();
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::user
     * @throws AuthenticationException
     */
    public function testUser(): void
    {
        $token = 'jwt';
        $userId = 1;
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->willReturn(new JWT(
                $token,
                new DataSet([]),
                new DataSet(['sub' => $userId])
            ));
        $this->userProvider->expects($this->once())
            ->method('retrieveById')
            ->with($userId)
            ->willReturn($user = $this->createMock(User::class));
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);

        $result = $jwtGuard->user();

        $this->assertSame($user, $result);
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::user
     * @throws AuthenticationException
     */
    public function testUserWillCache(): void
    {
        $token = 'jwt';
        $userId = 1;
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->willReturn(new JWT(
                $token,
                new DataSet([]),
                new DataSet(['sub' => $userId])
            ));
        $this->userProvider->expects($this->once())
            ->method('retrieveById')
            ->with($userId)
            ->willReturn($user = $this->createMock(User::class));
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);
        $jwtGuard->user();

        $result = $jwtGuard->user();

        $this->assertSame($user, $result);
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::user
     * @throws AuthenticationException
     */
    public function testUserWithNoSub(): void
    {
        $token = 'jwt';
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->willReturn(new JWT(
                $token,
                new DataSet([]),
                new DataSet([])
            ));
        $this->userProvider->expects($this->once())
            ->method('retrieveById')
            ->with(null)
            ->willReturn(null);
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);

        $result = $jwtGuard->user();

        $this->assertNull($result);
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::id
     * @throws AuthenticationException
     */
    public function testIdWithoutSub(): void
    {
        $token = 'jwt';
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->willReturn(new JWT($token, new DataSet([]), new DataSet([])));
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);

        $result = $jwtGuard->id();

        $this->assertNull($result);
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::id
     * @throws AuthenticationException
     */
    public function testIdWithSub(): void
    {
        $token = 'jwt';
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->willReturn(new JWT($token, new DataSet([]), new DataSet(['sub' => '1'])));
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);

        $result = $jwtGuard->id();

        $this->assertSame(1, $result);
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::id
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function testIdWithFailingAuthentication(): void
    {
        $this->expectException(AuthenticationException::class);

        $token = 'jwt';
        $this->jwtVerifier->expects($this->once())
            ->method('verify')
            ->with($token)
            ->will($this->throwException(new JWTVerifierException));
        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, $token);

        $jwtGuard->id();
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::validate
     */
    public function testValidate(): void
    {
        $this->expectException(MethodNotImplementedException::class);

        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, '');

        $jwtGuard->validate();
    }

    /**
     * @covers \Batenburg\JWTGuard\Guards\JWTGuard::setUser
     */
    public function testSetUser(): void
    {
        $this->expectException(MethodNotImplementedException::class);

        $jwtGuard = new JWTGuard($this->userProvider, $this->jwtVerifier, '');

        $jwtGuard->setUser($this->createMock(Authenticatable::class));
    }
}
