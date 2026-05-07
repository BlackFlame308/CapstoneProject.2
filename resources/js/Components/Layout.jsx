import { Link, usePage } from '@inertiajs/react';

export default function Layout({ children, title }) {
    const { auth } = usePage().props;
    const user = auth?.user ?? null;
    const permissions = user?.permissions ?? {};

    const navLink = (href, label) => (
        <li className="mb-2">
            <Link href={href} className="nav-link block px-3 py-2 rounded hover:bg-gray-100 transition leading-snug break-words">
                {label}
            </Link>
        </li>
    );

    return (
        <div className="min-h-screen bg-gray-50">
            <nav className="bg-[#000000] text-white shadow">
                <div className="container mx-auto px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <Link href="/dashboard" className="text-xl font-bold">SafeTrack</Link>
                    <div className="flex flex-wrap items-center gap-2 sm:gap-4">
                        {user && (
                            <>
                                <span className="text-sm opacity-90 max-w-56 truncate">{user.name}</span>
                                <Link
                                    href="/password/change"
                                    className="text-sm bg-white/15 hover:bg-white/25 px-3 py-1 rounded transition"
                                >
                                    Change Password
                                </Link>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    className="text-sm bg-white/15 hover:bg-white/25 px-3 py-1 rounded transition"
                                >
                                    Logout
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </nav>

            <div className="flex flex-col md:flex-row">
                {user && (
                    <aside className="w-full md:w-64 bg-white md:min-h-screen shadow-sm p-4 shrink-0">
                        <ul className="grid grid-cols-2 sm:grid-cols-3 md:block gap-x-2">
                            {navLink('/dashboard', 'Dashboard')}
                            {permissions.view_households && (
                                <>
                                    {navLink('/households', 'Households')}
                                    {permissions.manage_households && navLink('/households/create', 'Add Household')}
                                    {permissions.upload_csv && navLink('/csv/upload', 'Upload CSV')}
                                </>
                            )}
                            {permissions.manage_accounts && navLink('/accounts', 'Accounts')}
                            {permissions.register_accounts && navLink('/register', 'Register Account')}
                            {permissions.view_reports && navLink('/dashboard', 'System Reports')}
                            <li className="mt-0 md:mt-4 md:border-t md:pt-4">
                                {navLink('/password/change', 'Change Password')}
                            </li>
                        </ul>
                    </aside>
                )}

                <main className="flex-1 min-w-0 p-4 sm:p-6">
                    {title && <h1 className="text-2xl font-bold mb-6 break-words">{title}</h1>}
                    {children}
                </main>
            </div>
        </div>
    );
}
