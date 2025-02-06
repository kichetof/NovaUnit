<?php

namespace JoshGaber\NovaUnit\Actions;

use Illuminate\Testing\Constraints\ArraySubset;
use JoshGaber\NovaUnit\Constraints\IsActionResponseType;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\Responses\Visit;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Constraint\IsType;

class MockActionResponse
{
    private $response;

    public function __construct($response = null)
    {
        $this->response = $response;
    }

    /**
     * Asserts the handle response is of the given type.
     *
     * @param  string  $type
     * @param  string  $message
     * @return $this
     */
    public function assertResponseType(string $type, string $message = ''): self
    {
        PHPUnit::assertThat(
            $this->response,
            PHPUnit::logicalAnd(
                is_array($this->response)
                    ? new IsType('array')
                    : new IsInstanceOf(ActionResponse::class),
                new IsActionResponseType($type, $this->response)
            ),
            $message
        );

        return $this;
    }

    /**
     * Asserts the handle response is of type "message".
     *
     * @param string $message
     * @return $this
     */
    public function assertMessage(string $message = ''): self
    {
        return $this->assertResponseType('message', $message);
    }

    /**
     * Asserts the handle response is of type "danger".
     *
     * @param  string  $message
     * @return $this
     */
    public function assertDanger(string $message = ''): self
    {
        return $this->assertResponseType('danger', $message);
    }

    /**
     * Asserts the handle response is of type "deleted".
     *
     * @param string $message
     * @return $this
     */
    public function assertDeleted(string $message = ''): self
    {
        return $this->assertResponseType('deleted', $message);
    }

    /**
     * Asserts the handle response is of type "redirect".
     *
     * @param  string  $message
     * @return $this
     */
    public function assertRedirect(string $message = ''): self
    {
        return $this->assertResponseType('redirect', $message);
    }

    /**
     * Asserts the handle response is of type "push".
     *
     * @param  string  $message
     * @return $this
     */
    public function assertPush(string $message = ''): self
    {
        return $this->assertResponseType('json', $message);
    }

    /**
     * Asserts the handle response is of type "visit".
     *
     * @param string $path
     * @param array $options
     * @return $this
     */
    public function assertVisit(string $path = '', array $options = []): self
    {
        if (blank($path)) {
            return $this->assertResponseType('visit');
        }

        PHPUnit::assertArrayHasKey('visit', $this->response);

        $visit = $this->response['visit'];

        PHPUnit::logicalAnd(
            PHPUnit::assertNotEmpty($visit),
            PHPUnit::assertInstanceOf(Visit::class, $visit),
            PHPUnit::assertEquals($options, $visit->options),
            PHPUnit::assertEquals($path, $visit->path),
        );

        return $this;
    }

    /**
     * Asserts the handle response is of type "openInNewTab".
     *
     * @param string $message
     * @return $this
     */
    public function assertOpenInNewTab(string $message = ''): self
    {
        return $this->assertResponseContainsArray(['openInNewTab' => true], 'redirect', $message);
    }

    /**
     * Asserts the handle response of type "openInNewTab" directs to the specified path.
     */
    public function assertOpenInNewTabToPath(string $path, string $message = ''): self
    {
        return $this->assertResponseContainsArray(['url' => $path, 'openInNewTab' => true], 'redirect', $message);
    }

    /**
     * Asserts the handle response is of type "download".
     *
     * @param string $message
     * @return $this
     */
    public function assertDownload(string $message = ''): self
    {
        return $this->assertResponseType('download', $message);
    }

    private function assertResponseKeyContains(string $contents, string $type, string $key, string $message = ''): self
    {
        PHPUnit::assertThat(
            $this->response[$type]?->{$key} ?? '',
            PHPUnit::logicalAnd(
                PHPUnit::logicalNot(PHPUnit::isEmpty()),
                PHPUnit::stringContains($contents, true)
            ),
            $message
        );

        return $this;
    }

    private function assertResponseContainsArray(array $contents, string $type, string $message = ''): self
    {
        PHPUnit::assertThat(
            $this->response[$type]?->jsonSerialize() ?? throw new AssertionFailedError(),
            PHPUnit::logicalAnd(
                PHPUnit::logicalNot(PHPUnit::isEmpty()),
                new ArraySubset($contents, false)
            ),
            $message
        );

        return $this;
    }

    /**
     * Asserts the handle response is a "message" and contains the given text.
     *
     * @param string $contents The text to assert is in the response
     * @param string $message
     * @return $this
     */
    public function assertMessageContains(string $contents, string $message = ''): self
    {
        return $this->assertResponseKeyContains($contents, 'message', 'text', $message);
    }

    /**
     * Asserts the handle response is a "danger" and contains the given text.
     *
     * @param string $contents The text to assert is in the response
     * @param string $message
     * @return $this
     */
    public function assertDangerContains(string $contents, string $message = ''): self
    {
        return $this->assertResponseKeyContains($contents, 'danger', 'text', $message);
    }

    /**
     * @return array|ActionResponse|null
     */
    public function getResponse(): array|ActionResponse|null
    {
        return $this->response;
    }
}
