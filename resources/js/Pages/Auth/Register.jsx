import { useForm, Link } from '@inertiajs/react';

export default function Register({ roles, isFirstUser }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role_id: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/register');
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-700">
            <div className="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md">
                <h1 className="text-2xl font-bold text-center text-gray-800 mb-2">
                    {isFirstUser ? 'Setup Captain Account' : 'Register Account'}
                </h1>
                <p className="text-center text-gray-500 mb-6">
                    {isFirstUser ? 'Create the first admin account' : 'Create a new user account'}
                </p>

                <form onSubmit={submit}>
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input
                            type="text"
                            className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            value={data.name}
                            onChange={e => setData('name', e.target.value)}
                            required
                        />
                        {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                    </div>

                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            type="email"
                            className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            value={data.email}
                            onChange={e => setData('email', e.target.value)}
                            required
                        />
                        {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email}</p>}
                    </div>

                    {isFirstUser === false && (
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select
                                className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                value={data.role_id}
                                onChange={e => setData('role_id', e.target.value)}
                                required
                            >
                                <option value="">Select Role</option>
                                {roles.map(role => (
                                    <option key={role.id} value={role.id}>{role.name}</option>
                                ))}
                            </select>
                            {errors.role_id && <p className="text-red-500 text-xs mt-1">{errors.role_id}</p>}
                        </div>
                    )}

                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            type="password"
                            className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            value={data.password}
                            onChange={e => setData('password', e.target.value)}
                            required
                        />
                        {errors.password && <p className="text-red-500 text-xs mt-1">{errors.password}</p>}
                    </div>

                    <div className="mb-6">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input
                            type="password"
                            className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            value={data.password_confirmation}
                            onChange={e => setData('password_confirmation', e.target.value)}
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition disabled:opacity-50"
                    >
                        {processing ? 'Creating...' : (isFirstUser ? 'Create Account' : 'Register')}
                    </button>
                </form>

                {isFirstUser === false && (
                    <p className="text-center text-sm text-gray-500 mt-4">
                        Already have an account?
                        <Link href="/login" className="text-indigo-600 hover:text-indigo-800 font-medium ml-1">
                            Sign in
                        </Link>
                    </p>
                )}
            </div>
        </div>
    );
}
