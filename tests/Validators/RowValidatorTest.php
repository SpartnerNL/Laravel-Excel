<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Validators;

use Illuminate\Contracts\Validation\Factory;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Validators\RowValidator;
use ReflectionMethod;
use stdClass;

final class RowValidatorTest extends TestCase
{
    protected RowValidator $validator;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new RowValidator(app(Factory::class));
    }

    public function test_format_rule_with_array_input(): void
    {
        $rules = ['rule1', 'rule2'];

        $result = $this->callPrivateMethod('formatRule', [$rules]);

        $this->assertSame($rules, $result);
    }

    public function test_format_rule_with_object_input(): void
    {
        $rule = new stdClass;

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertSame($rule, $result);
    }

    public function test_format_rule_with_callable_input(): void
    {
        $rule = (fn (): string => 'callable');

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertSame($rule, $result);
    }

    public function test_format_rule_with_required_without_all(): void
    {
        $rule = 'required_without_all:first_name,last_name';

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertSame('required_without_all:*.first_name,*.last_name', $result);
    }

    public function test_format_rule_with_required_without(): void
    {
        $rule = 'required_without:first_name';

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertSame('required_without:*.first_name', $result);
    }

    public function test_format_rule_with_string_input_not_matching_pattern(): void
    {
        $rule = 'rule';

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertSame($rule, $result);
    }

    /**
     * Call a private function.
     *
     * @param  array<mixed>  $args
     */
    public function callPrivateMethod(string $name, array $args): mixed
    {
        return (new ReflectionMethod(RowValidator::class, $name))->invokeArgs($this->validator, $args);
    }
}
