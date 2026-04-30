import Layout from '@/Components/Layout';
import { useForm, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

// Eye icon components
function EyeIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                   -1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    );
}

function EyeOffIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7
                   a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
                   M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29
                   M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7
                   a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
        </svg>
    );
}

function PasswordField({ id, label, value, onChange, error }) {
    const [show, setShow] = useState(false);
    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
            <div className="relative">
                <input
                    id={id}
                    type={show ? 'text' : 'password'}
                    className="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    value={value}
                    onChange={onChange}
                    autoComplete={id === 'current_password' ? 'current-password' : 'new-password'}
                    required
                />
                <button
                    type="button"
                    tabIndex={-1}
                    onClick={() => setShow(v => !v)}
                    className="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-indigo-600 transition"
                    aria-label={show ? 'Hide password' : 'Show password'}
                >
                    {show ? <EyeOffIcon /> : <EyeIcon />}
                </button>
            </div>
            {error && <p className="text-red-500 text-xs mt-1">{error}</p>}
        </div>
    );
}

export default function ChangePassword() {
    const { flash } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/password/change', {
            onSuccess: () => reset(),
        });
    };

    return (
        <Layout title="Change Password">
            <div className="max-w-md mx-auto bg-white rounded-lg shadow p-6">
                <h2 className="text-xl font-bold mb-1">Change Password</h2>
                <p className="text-gray-500 text-sm mb-6">Choose a strong new password for your account.</p>

                {flash?.success && (
                    <div className="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded text-sm">
                        ✅ {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded text-sm">
                        ❌ {flash.error}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-4">
                    <PasswordField
                        id="current_password"
                        label="Current Password"
                        value={data.current_password}
                        onChange={e => setData('current_password', e.target.value)}
                        error={errors.current_password}
                    />
                    <PasswordField
                        id="password"
                        label="New Password"
                        value={data.password}
                        onChange={e => setData('password', e.target.value)}
                        error={errors.password}
                    />
                    <PasswordField
                        id="password_confirmation"
                        label="Confirm New Password"
                        value={data.password_confirmation}
                        onChange={e => setData('password_confirmation', e.target.value)}
                        error={errors.password_confirmation}
                    />

                    <p className="text-xs text-gray-400">
                        Password must be at least 8 characters and include uppercase, lowercase, and numbers.
                    </p>

                    <div className="flex gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded transition disabled:opacity-50"
                        >
                            {processing ? 'Updating...' : 'Update Password'}
                        </button>
                        <Link href="/dashboard" className="text-gray-500 hover:underline py-2 text-sm">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </Layout>
    );
}
