<?php

namespace MySof;

use MySof\PasswordCheck\Exception\NoResponse as NoResponseException;
use MySof\PasswordCheck\Exception\UnexpectedResponse as UnexpectedResponseException;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class PasswordCheckTest
 *
 * @author Mike Smith <mail@mikegsmith.co.uk>
 * @package MySof
 */
class PasswordCheckTest extends TestCase
{
    protected $sampleResponse = <<<RESPONSE
D09CA3762AF61E59520943DC26494F8941B:5
1E4C9B93F3F0682250B6CF8331B7EE68FD8:5
FB2927D828AF22F592134E8932480637C0D:5
73A05C0ED0176787A4F1574FF0075F7521E:5
C1D808E04732ADF679965CCC34CA7AE3441:5
37D0679CA88DB6464EAC60DA96345513964:5
DA4D09E062AA5E4A390B0A572AC0D2C0220:5
BF07DC1BE38B20CD6E46949A1071F9D0E3D:5
E5D64B0E216796E834F52D61FD0B70332FC:5
8B1797B72ACFFF9595A5A2A373EC3D9106D:5
889667EFAEBB33B8C12572835DA3F027F78:5
1C8C6DEA98958C219F6F2D038C44DC5D362:5
48DD193D56EA7B0BAAD25B19455E529F5EE:5
62C597EC858F6E7B54E7E58525E6A95E6D8:5
24BDC7452E55738DEB5F868E1F16DEA5ACE:5
5FC1EA228B9061041B7CEC4BD3C52AB3CE3:5
1FCCB586DC39E1CE34BB482F0AFE557B49F:5
D832AF899035363A69FD53CD3BE8F71501C:5
EAFDB2367620A393C973EDDBE8F8B846EBD:5
78A0B9E25EE2F7C8B2F7AC92B6A74B3F9C5:5
D2029F64D445BD131FFAA399A42D2F8E7DC:5
2B4A77A9524D675DAD27C3276AB5705E5E8:5
E9C6273385EA69892C48C80AA6CB25B9113:5
7ACBA4F54F55AAFC33BB06BBBF6CA803E9A:5
1C64588C7FA6419B4D29DC1F4426279BA01:5
F9C1C1DA1394D6D34B248C51BE2AD740840:5
406781EBFDF7161BBBB18E16CB9AD1F3BE4:5
604DD31094A8D69DAE60F1BCD347F1AFC5A:5
B6BA9E0939583F973BC1682493351AD4FE8:5
1ACBF060DDA5FC7260D05A5924A34E4C0E7:5
B87EA9EB7A32FD4057276D3A1FAB861C1D5:5
E0C99BF7D689CE71C360699A14CE2F99774:5
ED014AEC7623A54F0591DA07A85FD4B762D:5
671CBC500627EA424EEA5F91996221B5935:5
461C607C33229772D402505601016A7D0EA:5
478180D07080D5E4F3BAA0099996C364162:5
1BE8B70E435C65AEF8BA9798FF7775C361E:5
D5A9E45420321F44C72DA5D90D7F0432FFB:5
F6469FC3E1ACFB9F2BDBFC5A3D2BBB8E2AD:5
1B22793A81569C94CA17E4D9C293D8E201F:5
RESPONSE;

    public function testWillThrowNoResponseExceptionIfNetworkRequestFails()
    {
        $this->expectException(NoResponseException::class);

        $passwordChecker = new PasswordCheck(
            $this->getMockHttpClient(
                $this->getMockFailedHttpResponse()
            )
        );

        $passwordChecker->isSafe("password");
    }

    public function testWillThrowUnexpectedResponseExceptionIfAnUnexpectedHttpStatusIsReceived()
    {
        $this->expectException(UnexpectedResponseException::class);

        $passwordChecker = new PasswordCheck(
            $this->getMockHttpClient(
                $this->getMockHttpResponse(500)
            )
        );

        $passwordChecker->isSafe("password");
    }

    public function testWillThrowUnexpectedResponseExceptionIfANotFoundHttpStatusIsReceived()
    {
        $this->expectException(UnexpectedResponseException::class);

        $passwordChecker = new PasswordCheck(
            $this->getMockHttpClient(
                $this->getMockHttpResponse(404)
            )
        );

        $this->assertTrue($passwordChecker->isSafe("password"));
    }

    public function testPasswordIsCleanIfHashDoesNotAppearInTheResults()
    {
        $passwordChecker = new PasswordCheck(
            $this->getMockHttpClient(
                $this->getMockHttpResponse(200, $this->sampleResponse)
            )
        );

        $this->assertTrue($passwordChecker->isSafe("clean"));
    }

    public function testPasswordIsDirtyIfHashAppearsInResults()
    {
        $passwordChecker = new PasswordCheck(
            $this->getMockHttpClient(
                $this->getMockHttpResponse(200, $this->sampleResponse)
            )
        );

        $this->assertFalse($passwordChecker->isSafe("password"));
    }

    protected function getMockHttpClient(ResponseInterface $response): HttpClientInterface
    {
        $httpClient = $this->getMockBuilder(HttpClientInterface::class)
            ->getMock();

        $httpClient
            ->method("request")
            ->willReturn($response);

        return $httpClient;
    }

    public function getMockHttpResponse(int $statusCode = 200, string $content = ""): ResponseInterface
    {
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response
            ->method("getStatusCode")
            ->willReturn($statusCode);

        $response
            ->method("getContent")
            ->willReturn($content);

        return $response;
    }

    public function getMockFailedHttpResponse(): ResponseInterface
    {
        $mockException = $this->getMockBuilder(TransportExceptionInterface::class)->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response
            ->method("getStatusCode")
            ->willThrowException($mockException);

        $response
            ->method("getContent")
            ->willThrowException($mockException);

        return $response;
    }
}
