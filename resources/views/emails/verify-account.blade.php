<h3>Hi: {{ $account->name }}</h3>

<p>
    haha
</p>

<p>
    <a href="{{ route('account.verify', $account->email) }}">Click here to verify your account</a>
</p>