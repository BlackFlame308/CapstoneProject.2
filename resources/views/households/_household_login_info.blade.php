<div class="alert alert-info">
    <h6>Household Login Credentials</h6>
    <p><strong>Email:</strong> <code>{{ $household->user->email ?? $household->household_code.'@households.capstone.local' }}</code></p>
    @if(isset($household->user) && $household->user->temp_password)
    <p><strong>Temporary Password:</strong> <code>{{ $household->user->temp_password }}</code> <small>(Share with head, change on first login)</small></p>
    @else
    <p><strong>Temporary Password:</strong> Contact admin</p>
    @endif
    <p class="small text-muted">Login at /login</p>
</div>
