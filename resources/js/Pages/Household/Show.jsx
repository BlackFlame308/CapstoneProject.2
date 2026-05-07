import Layout from '@/Components/Layout';
import { usePage, Link } from '@inertiajs/react';

export default function HouseholdShow() {
    const { household, householdAccount, flash } = usePage().props;
    const barangay = household?.address?.barangay;
    const city = barangay?.city;
    const province = city?.province;
    const region = province?.region;

    const badgeColor = (badge) => {
        if (badge === 'Critical') return 'bg-[#EF4444] text-white';
        if (badge === 'High') return 'bg-[#3B82F6] text-white';
        return 'bg-[#000000] text-white';
    };

    const handleDelete = (e) => {
        if (!confirm('Are you sure you want to delete this household?')) {
            e.preventDefault();
        }
    };

    return (
        <Layout title={`Household ${household?.household_code}`}>
            {flash?.success && (
                <div className="mb-4 p-3 bg-[#3B82F6] text-white rounded">{flash.success}</div>
            )}

            <div className="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
                <div className="flex justify-between items-start mb-6">
                    <div>
                        <h2 className="text-2xl font-bold">{household?.household_name || household?.household_code}</h2>
                        <span className="text-sm text-gray-500 mr-3">{household?.household_code}</span>
                        <span className={`inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium ${badgeColor(household?.vulnerability_badge)}`}>
                            {household?.vulnerability_badge} Vulnerability
                        </span>
                    </div>
                    <div className="flex gap-2">
                        <Link
                            href={`/households/${household?.id}/edit`}
                            className="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm transition"
                        >
                            Edit
                        </Link>
                        <Link
                            href={`/households/${household?.id}`}
                            method="delete"
                            as="button"
                            type="button"
                            className="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded text-sm transition"
                            onClick={handleDelete}
                        >
                            Delete
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 className="text-sm font-semibold text-gray-500 uppercase mb-2">Address</h3>
                        {household?.address?.full_address && (
                            <p className="text-gray-800 italic mb-1">{household.address.full_address}</p>
                        )}
                        <p className="text-gray-800">{household?.address?.street || '-'}</p>
                        <p className="text-gray-800">{household?.address?.purok_sitio || '-'}</p>
                        <p className="text-gray-800">{barangay?.name}, {city?.name}</p>
                        <p className="text-gray-800">{province?.name}, {region?.name}</p>
                    </div>
                    <div>
                        <h3 className="text-sm font-semibold text-gray-500 uppercase mb-2">Contact</h3>
                        <p className="text-gray-800">Email: {household?.email || 'N/A'}</p>
                        <p className="text-gray-800">Phone: {household?.contact_number || 'N/A'}</p>
                        <p className="text-gray-500 text-sm">Emergency: {household?.emergency_contact || 'N/A'}</p>
                    </div>
                </div>

                {householdAccount && (
                    <div className="bg-[#F7F9FB] border border-gray-200 rounded p-4 mb-8">
                        <h3 className="text-sm font-semibold text-gray-700 uppercase mb-2">Household Login</h3>
                        <p className="text-gray-800 break-all">Email: {householdAccount.email}</p>
                        <p className="text-gray-800 break-all">Temporary Password: {householdAccount.temp_password || 'Already changed / not available'}</p>
                    </div>
                )}

                <div className="grid grid-cols-3 gap-4 mb-8">
                    <div className="bg-[#F7F9FB] border border-gray-200 rounded p-4 text-center">
                        <div className="text-2xl font-bold text-[#000000]">{household?.population ?? 0}</div>
                        <div className="text-xs text-gray-600">Population</div>
                    </div>
                    <div className="bg-[#F7F9FB] border border-gray-200 rounded p-4 text-center">
                        <div className="text-2xl font-bold text-[#EF4444]">{household?.vulnerable_count ?? 0}</div>
                        <div className="text-xs text-gray-600">Vulnerable</div>
                    </div>
                    <div className="bg-[#F7F9FB] border border-gray-200 rounded p-4 text-center">
                        <div className="text-2xl font-bold text-[#3B82F6]">{household?.vulnerability_score ?? 0}</div>
                        <div className="text-xs text-gray-600">Vulnerability Score</div>
                    </div>
                </div>

                <h3 className="text-lg font-semibold mb-4">Members</h3>
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="px-4 py-2 text-left">Name</th>
                                <th className="px-4 py-2 text-left">Relation</th>
                                <th className="px-4 py-2 text-left">Age</th>
                                <th className="px-4 py-2 text-left">Sex</th>
                                <th className="px-4 py-2 text-left">Civil Status</th>
                                <th className="px-4 py-2 text-left">Education</th>
                                <th className="px-4 py-2 text-left">Occupation</th>
                                <th className="px-4 py-2 text-left">Tags</th>
                            </tr>
                        </thead>
                        <tbody>
                            {household?.members?.map((m, i) => (
                                <tr key={i} className="border-b">
                                    <td className="px-4 py-2">{m.full_name || m.name || [m.first_name, m.middle_name, m.last_name].filter(Boolean).join(' ')}</td>
                                    <td className="px-4 py-2">{m.relation || '-'}</td>
                                    <td className="px-4 py-2">{m.age}</td>
                                    <td className="px-4 py-2">{m.sex}</td>
                                    <td className="px-4 py-2">{m.civil_status || '-'}</td>
                                    <td className="px-4 py-2">{m.education_level || '-'}</td>
                                    <td className="px-4 py-2">{m.occupation || '-'}</td>
                                    <td className="px-4 py-2">
                                        {m.is_pwd && <span className="inline-block bg-[#3B82F6] text-white text-xs px-2 rounded-full mr-1">PWD</span>}
                                        {m.is_pregnant && <span className="inline-block bg-[#EF4444] text-white text-xs px-2 rounded-full">Pregnant</span>}
                                    </td>
                                </tr>
                            ))}
                            {(!household?.members || household.members.length === 0) && (
                                <tr>
                                    <td colSpan="6" className="px-4 py-4 text-center text-gray-500">No members recorded.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </Layout>
    );
}
