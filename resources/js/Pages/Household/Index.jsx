import Layout from '@/Components/Layout';
import { usePage, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function HouseholdIndex() {
    const { households, filters, flash } = usePage().props;
    
    const [search, setSearch] = useState(filters?.search || '');
    const [purokSitio, setPurokSitio] = useState(filters?.purok_sitio || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/households', { search, purok_sitio: purokSitio }, { preserveState: true, replace: true });
    };

    return (
        <Layout title="Households">
            {flash?.success && (
                <div className="mb-4 p-3 bg-green-100 text-green-800 rounded">{flash.success}</div>
            )}
            {flash?.error && (
                <div className="mb-4 p-3 bg-red-100 text-red-800 rounded">{flash.error}</div>
            )}

            <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <h2 className="text-xl font-semibold">All Households</h2>
                
                <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <input
                        type="text"
                        placeholder="Search code or head name..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    />
                    <input
                        type="text"
                        placeholder="Filter by Sitio/Purok..."
                        value={purokSitio}
                        onChange={(e) => setPurokSitio(e.target.value)}
                        className="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    />
                    <button type="submit" className="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded transition">
                        Filter
                    </button>
                    <Link
                        href="/households/create"
                        className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded transition text-center"
                    >
                        + Add Household
                    </Link>
                </form>
            </div>

            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="min-w-full text-sm">
                    <thead className="bg-gray-100">
                        <tr>
                            <th className="px-4 py-3 text-left">Code</th>
                            <th className="px-4 py-3 text-left">Location</th>
                            <th className="px-4 py-3 text-left">Contact</th>
                            <th className="px-4 py-3 text-left">Members</th>
                            <th className="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {households?.data?.map((hh) => {
                            const barangay = hh.address?.barangay;
                            const city = barangay?.city;
                            const province = city?.province;
                            const region = province?.region;
                            
                            return (
                                <tr key={hh.id} className="border-b hover:bg-gray-50">
                                    <td className="px-4 py-3 font-medium">{hh.household_code}</td>
                                    <td className="px-4 py-3">
                                        {hh.address?.street} {hh.address?.purok_sitio}
                                        <br />
                                        <span className="text-xs text-gray-500">
                                            {barangay?.name}, {city?.name}, {province?.name}, {region?.name}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">{hh.contact_number || '-'}</td>
                                    <td className="px-4 py-3">{hh.population ?? hh.members?.length ?? 0}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            <Link
                                                href={`/households/${hh.id}`}
                                                className="text-indigo-600 hover:underline text-xs"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                href={`/households/${hh.id}/edit`}
                                                className="text-gray-600 hover:underline text-xs"
                                            >
                                                Edit
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            );
                        })}
                        {(!households?.data || households.data.length === 0) && (
                            <tr>
                                <td colSpan="5" className="px-4 py-6 text-center text-gray-500">
                                    No households found.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {/* Pagination */}
            {households?.links && (
                <div className="mt-4 flex flex-wrap gap-2">
                    {households.links.map((link, i) => (
                        <Link
                            key={i}
                            href={link.url || '#'}
                            className={`px-3 py-1 rounded text-sm ${
                                link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                            } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                            preserveState
                        />
                    ))}
                </div>
            )}
        </Layout>
    );
}

