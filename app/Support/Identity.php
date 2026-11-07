<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * One definition of a valid person on Sharet: a real name, a real email address and a real East African mobile
 * number. Forms use rules() (after clean()) so people see a clear message; the User model calls assertValid() on
 * every save, so no path (admin, website forms, seeders, tinker) can store a bad name, email or phone.
 */
class Identity
{
    /** Two or more words of letters (any alphabet, accents, ' ’ . -), single spaces, 3 to 120 characters. No digits. */
    public const NAME = '/^(?=.{3,120}$)\p{L}[\p{L}\p{M}\'’.\-]*(?: \p{L}[\p{L}\p{M}\'’.\-]*)+$/u';

    /** Local part and domain in the usual shape; no leading/trailing or doubled dots; a real top-level domain. */
    public const EMAIL = '/^(?!.*\.\.)[A-Za-z0-9](?:[A-Za-z0-9._%+\-]{0,62}[A-Za-z0-9])?@(?:[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,24}$/';

    /** Mobile numbers in international form: Kenya +2547/+2541, Uganda +2567, Tanzania +2556/+2557, Rwanda +25072/3/8/9. */
    public const PHONE = '/^\+(?:254[17]\d{8}|2567\d{8}|255[67]\d{8}|2507[2389]\d{7})$/';

    public const MESSAGES = [
        'name.regex' => 'Enter your full name as on your ID: first and last name, letters only.',
        'email.regex' => 'Enter a real email address, like name@example.com.',
        'email.email' => 'Enter a real email address, like name@example.com.',
        'phone.regex' => 'Enter a mobile number in Kenya, Uganda, Tanzania or Rwanda, like 0712 345 678 or +256 772 123 456.',
    ];

    public static function normaliseName(?string $name): ?string
    {
        $name = trim(preg_replace('/\s+/u', ' ', (string) $name));

        return $name === '' ? null : $name;
    }

    public static function normaliseEmail(?string $email): ?string
    {
        $email = strtolower(trim((string) $email));

        return $email === '' ? null : $email;
    }

    /** "0712 345 678", "254712345678", "712345678", "+256-772-123-456" → "+254712345678" / "+256772123456". */
    public static function normalisePhone(?string $phone): ?string
    {
        $digits = preg_replace('/[^\d+]/', '', (string) $phone);
        if ($digits === '' || $digits === '+') {
            return null;
        }
        $digits = ltrim($digits, '+');

        return match (true) {
            str_starts_with($digits, '00') => '+'.substr($digits, 2),
            preg_match('/^0[17]\d{8}$/', $digits) === 1 => '+254'.substr($digits, 1),   // Kenyan local
            preg_match('/^[17]\d{8}$/', $digits) === 1 => '+254'.$digits,                 // Kenyan without the 0
            default => '+'.$digits,
        };
    }

    /** Normalise name, email and phone on the request before validating. */
    public static function clean(Request $request): void
    {
        $merge = [];
        foreach (['name' => 'normaliseName', 'email' => 'normaliseEmail', 'phone' => 'normalisePhone'] as $field => $fn) {
            if ($request->has($field)) {
                $merge[$field] = self::$fn($request->input($field));
            }
        }
        $request->merge($merge);
    }

    /**
     * Validation rules. $unique: check the users table (ignore $ignoreId when editing). $phoneRequired: the phone
     * is the M-PESA number for personal accounts, so most forms require it.
     */
    public static function rules(bool $phoneRequired = true, bool $unique = true, ?int $ignoreId = null): array
    {
        $email = ['required', 'string', 'max:190', 'regex:'.self::EMAIL, app()->isProduction() && config('sharet.email_dns_check', true) ? 'email:rfc,dns' : 'email:rfc'];
        $phone = [$phoneRequired ? 'required' : 'nullable', 'string', 'regex:'.self::PHONE];
        if ($unique) {
            $email[] = Rule::unique('users', 'email')->ignore($ignoreId);
            $phone[] = Rule::unique('users', 'phone')->ignore($ignoreId);
        }

        return [
            'name' => ['required', 'string', 'regex:'.self::NAME],
            'email' => $email,
            'phone' => $phone,
        ];
    }

    /** Problems with a user's name, email or phone (only fields being created or changed). */
    public static function problems(User $user): array
    {
        $check = fn (string $field) => ! $user->exists || $user->isDirty($field);
        $problems = [];

        if ($check('name') && ! preg_match(self::NAME, (string) $user->name)) {
            $problems['name'] = self::MESSAGES['name.regex'];
        }
        if ($check('email') && (! preg_match(self::EMAIL, (string) $user->email) || ! filter_var($user->email, FILTER_VALIDATE_EMAIL))) {
            $problems['email'] = self::MESSAGES['email.regex'];
        }
        if ($check('phone') && $user->phone !== null && ! preg_match(self::PHONE, (string) $user->phone)) {
            $problems['phone'] = self::MESSAGES['phone.regex'];
        }

        return $problems;
    }

    /** Called by the User model before every save: refuses invalid people, wherever they come from. */
    public static function assertValid(User $user): void
    {
        // A business wallet is an account, not a person: it carries the company's name and can never sign in
        if ($user->is_business_wallet) {
            if (trim((string) $user->name) === '') {
                throw ValidationException::withMessages(['name' => 'A business wallet needs the business name.']);
            }

            return;
        }
        if ($user->isDirty('name')) {
            $user->name = self::normaliseName($user->name);
        }
        if ($user->isDirty('email')) {
            $user->email = self::normaliseEmail($user->email);
        }
        if ($user->isDirty('phone')) {
            $user->phone = self::normalisePhone($user->phone);
        }

        if ($problems = self::problems($user)) {
            throw ValidationException::withMessages($problems);
        }
    }
}
