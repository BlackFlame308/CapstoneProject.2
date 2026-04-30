import Layout from '@/Components/Layout';
import { Link } from '@inertiajs/react';

export default function AccountIndex({ users, filters }) {
    return (
        <Layout title="Manage Accounts">
            <div className="bg-white rounded-lg shadow">
                <div className="p-6 border-b flex justify-between items-center">
                    <h2 className="text-lg font-semibold">User Accounts</h2>
                    <div className="flex gap-2">
                        <form method="GET" className="flex gap-2">
                            <input
                                type="search"
                                name="search"
                                defaultValue={filters?.search}
                                placeholder="Search users..."
                                className="border rounded px-3 py-2 text-sm"
                            />
                            <button type="submit" className="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded text-sm">
                                Search
                            </button>
                        </form>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left">
                        <thead className="bg-gray-50 text-gray-700 uppercase text-xs">
                            <tr>
                                <th className="px-6 py-3">Name</th>
                                <th className="px-6 py-3">Email</th>
                                <th className="px-6 py-3">Role</th>
                                <th className="px-6 py-3">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.map((user) => (
                                <tr key={user.id} className="border-b hover:bg-gray-50">
                                    <td className="px-6 py-4 font-medium">{user.name}</td>
                                    <td className="px-6 py-4">{user.email}</td>
                                    <td className="px-6 py-4">
                                        <span className={`px-2 py-1 rounded-full text-xs ${getRoleBadge(user.role?.name)}`}>
                                            {user.role?.name || 'None'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-gray-500">
                                        {new Date(user.created_at).toLocaleDateString()}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {users.links && (
                    <div className="p-4 border-t flex justify-end gap-1">
                        {users.links.map((link, i) => (
                            <Link
                                key={i}
                                href={link.url}
                                className={`px-3 py-1 rounded text-sm ${
                                    link.active
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-100 hover:bg-gray-200'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </Layout>
    );
}

function getRoleBadge(role) {
    switch (role) {
        case 'Super Admin': return 'bg-purple-100 text-purple-800';
        case 'Admin': return 'bg-blue-100 text-blue-800';
        case 'Captain': return 'bg-green-100 text-green-800';
        case 'Encoder': return 'bg-yellow-100 text-yellow-800';
        case 'Household': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
