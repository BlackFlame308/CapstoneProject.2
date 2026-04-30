import { Link, usePage } from '@inertiajs/react';

export default function Layout({ children, title }) {
    const { auth } = usePage().props;
    // Safely access user with fallback to null if any property is undefined
    const user = auth?.user ?? null;
    const permissions = user?.permissions ?? {};

    const navLink = (href, label) => (
        <li className="mb-2">
            <Link href={href} className="nav-link block px-3 py-2 rounded hover:bg-gray-100 transition">
                {label}
            </Link>
        </li>
    );

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Navbar */}
            <nav className="bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow">
                <div className="container mx-auto px-4 py-3 flex items-center justify-between">
                    <Link href="/dashboard" className="text-xl font-bold">SafeTrack</Link>
                    <div className="flex items-center gap-4">
                        {user && (
                            <>
                                <span className="text-sm opacity-90">{user.name}</span>
                                <Link
                                    href="/password/change"
                                    className="text-sm bg-white/20 hover:bg-white/30 px-3 py-1 rounded transition"
                                >
                                    Change Password
                                </Link>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    className="text-sm bg-white/20 hover:bg-white/30 px-3 py-1 rounded transition"
                                >
                                    Logout
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </nav>

            <div className="flex">
                {/* Sidebar */}
                {user && (
                    <aside className="w-64 bg-white min-h-screen shadow-sm p-4">
                        <ul>
                            {navLink('/dashboard', '🏠 Dashboard')}
                            {permissions.view_households && (
                                <>
                                    {navLink('/households', '👨‍👩‍👧 Households')}
                                    {permissions.manage_households && navLink('/households/create', '➕ Add Household')}
                                    {navLink('/csv/upload', '📂 Upload CSV')}
                                </>
                            )}
                            {permissions.manage_accounts && navLink('/accounts', '👤 Accounts')}
                            {permissions.register_accounts && navLink('/register', '📝 Register Account')}
                            {permissions.view_reports && navLink('/dashboard', '📊 System Reports')}
                            <li className="mt-4 border-t pt-4">
                                {navLink('/password/change', '🔑 Change Password')}
                            </li>
                        </ul>
                    </aside>
                )}

                {/* Main Content */}
                <main className="flex-1 p-6">
                    {title && <h1 className="text-2xl font-bold mb-6">{title}</h1>}
                    {children}
                </main>
            </div>
        </div>
    );
}

