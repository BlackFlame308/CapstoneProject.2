import { Link, usePage } from '@inertiajs/react';

export default function Layout({ children, title }) {
    const { auth } = usePage().props;
    // Safely access user with fallback to null if any property is undefined
    const user = auth?.user ?? null;
    const permissions = user?.permissions ?? {};

    const navLink = (href, label) => (
        <li className="mb-2">
            <Link href={href} className="nav-link block px-3 py-2 rounded hover:sidebar-active transition">
                {label}
            </Link>
        </li>
    );

    return (
        <div className="min-h-screen bg-[#F7F9FB]">
            {/* Navbar */}
            <nav className="bg-navbar text-white shadow">
                <div className="container mx-auto px-4 py-3 flex items-center justify-between">
                    <Link href="/dashboard" className="text-xl font-bold">SafeTrack</Link>
                    <div className="flex items-center gap-4">
                        {user && (
                            <>
                                <span className="text-white font-medium mr-2">{user.name}</span>
                                <Link
                                    href="/password/change"
                                    className="text-white border border-white/60 bg-transparent rounded-[6px] px-[14px] py-[6px] text-sm transition hover:border-[#3B82F6] hover:text-[#3B82F6]"
                                >
                                    Change Password
                                </Link>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    className="text-white bg-[#EF4444] rounded-[6px] px-[14px] py-[6px] text-sm font-medium transition hover:bg-[#DC2626]"
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
                    <aside className="w-64 bg-sidebar text-white min-h-screen shadow-sm p-4">
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
                            <li className="mt-4 border-t pt-4 border-white/20">
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

