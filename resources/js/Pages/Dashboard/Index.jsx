import Layout from '@/Components/Layout';
import { usePage, Link } from '@inertiajs/react';
import { PieChart, Pie, Cell, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const COLORS = ['#3B82F6', '#000000', '#EF4444'];

export default function Dashboard() {
    const {
        stats,
        barangayStats = [],
        recentHouseholds = [],
        membersByBarangay = [],
        ageDistribution,
        sitioVulnerability = [],
        flash,
        auth,
    } = usePage().props;
    const user = auth?.user ?? null;
    const permissions = user?.permissions ?? {};

    // Age distribution data
    const ageData = [
        { name: 'Children (0-17)', value: ageDistribution?.children ?? 0, color: COLORS[0] },
        { name: 'Adults (18-59)', value: ageDistribution?.adults ?? 0, color: COLORS[1] },
        { name: 'Seniors (60+)', value: ageDistribution?.seniors ?? 0, color: COLORS[2] },
    ];

    // Members by barangay for chart
    const barangayData = membersByBarangay.map(b => ({ name: b.name, population: b.count }));

    // Comprehensive stats cards
    const StatCard = ({ label, value, color = 'bg-[#3B82F6]' }) => (
        <div className={`${color} text-white rounded-lg p-5 shadow min-w-0`}>
            <div className="text-2xl md:text-3xl font-bold tabular-nums break-words">{Intl.NumberFormat().format(value ?? 0)}</div>
            <div className="text-sm opacity-90 mt-1 leading-snug">{label}</div>
        </div>
    );

    return (
        <Layout title="Dashboard">
            {flash?.success && (
                <div className="mb-4 p-3 bg-[#3B82F6] text-white rounded">{flash.success}</div>
            )}
            {flash?.error && (
                <div className="mb-4 p-3 bg-red-100 text-red-800 rounded">{flash.error}</div>
            )}

            {/* Main Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <StatCard label="Total Households" value={stats?.totalHouseholds} color="bg-primary" />
                <StatCard label="Total Population" value={stats?.totalMembers} color="bg-[#3B82F6]" />
                <StatCard label="PWD Count" value={stats?.totalPWD} color="bg-[#EF4444]" />
                <StatCard label="Senior Citizens" value={stats?.totalSeniors} color="bg-[#000000]" />
            </div>

            {/* Additional Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <StatCard label="Adults (18-59)" value={stats?.totalAdults} color="bg-[#3B82F6]" />
                <StatCard label="Children (0-17)" value={stats?.totalChildren} color="bg-[#000000]" />
                {permissions.manage_accounts && (
                    <>
                        <StatCard label="Total Users" value={stats?.totalUsers} color="bg-[#3B82F6]" />
                        <StatCard label="Captains" value={stats?.totalCaptains} color="bg-[#000000]" />
                    </>
                )}
            </div>

            {/* Charts */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="text-lg font-semibold mb-4">Population Distribution by Age</h3>
                    <ResponsiveContainer width="100%" height={300}>
                        <PieChart>
                            <Pie
                                data={ageData} 
                                dataKey="value" 
                                nameKey="name" 
                                cx="50%" 
                                cy="50%" 
                                outerRadius={95}
                            >
                                {ageData.map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={entry.color} />
                                ))}
                            </Pie>
                            <Tooltip />
                            <Legend />
                        </PieChart>
                    </ResponsiveContainer>
                </div>

                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="text-lg font-semibold mb-4">Population by Barangay</h3>
                    {barangayData.length > 0 ? (
                        <ResponsiveContainer width="100%" height={Math.max(300, barangayData.length * 34)}>
                            <BarChart data={barangayData} layout="vertical">
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis type="number" />
                                <YAxis dataKey="name" type="category" width={140} tick={{ fontSize: 12 }} />
                                <Tooltip />
                                <Bar dataKey="population" fill="#3B82F6" />
                            </BarChart>
                        </ResponsiveContainer>
                    ) : (
                        <div className="h-[300px] flex items-center justify-center text-gray-500">
                            No population data available
                        </div>
                    )}
                </div>
            </div>

            {/* Refresh Analytics */}
            <div className="bg-white rounded-lg shadow p-4 mb-8">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 className="text-lg font-semibold">Update Analytics</h3>
                        <p className="text-gray-600 text-sm">Refresh analytics data for all barangays based on current household and member data.</p>
                    </div>
                    <Link
                        href="/analytics/update"
                        method="post"
                        as="button"
                        className="bg-[#3B82F6] hover:bg-[#000000] text-white px-4 py-2 rounded transition whitespace-nowrap"
                    >
                        Refresh Analytics
                    </Link>
                </div>
            </div>

            {/* Comprehensive Barangay Stats Table */}
            <div className="bg-white rounded-lg shadow p-4 mb-8">
                <h3 className="text-lg font-semibold mb-4">Statistics by Barangay (Live Data)</h3>
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="px-3 py-2 text-left font-semibold">Barangay</th>
                                <th className="px-3 py-2 text-right font-semibold">Households</th>
                                <th className="px-3 py-2 text-right font-semibold">Population</th>
                                <th className="px-3 py-2 text-right font-semibold">Male</th>
                                <th className="px-3 py-2 text-right font-semibold">Female</th>
                                <th className="px-3 py-2 text-right font-semibold">Children</th>
                                <th className="px-3 py-2 text-right font-semibold">Adults</th>
                                <th className="px-3 py-2 text-right font-semibold">Seniors</th>
                                <th className="px-3 py-2 text-right font-semibold">PWD</th>
                            </tr>
                        </thead>
                        <tbody>
                            {barangayStats?.map((stat, i) => (
                                <tr key={i} className="border-b hover:bg-gray-50">
                                    <td className="px-3 py-2 font-medium">{stat.name}</td>
                                    <td className="px-3 py-2 text-right">{stat.households_count ?? 0}</td>
                                    <td className="px-3 py-2 text-right font-semibold">{stat.total_population ?? 0}</td>
                                    <td className="px-3 py-2 text-right">{stat.total_males ?? 0}</td>
                                    <td className="px-3 py-2 text-right">{stat.total_females ?? 0}</td>
                                    <td className="px-3 py-2 text-right">{stat.total_children ?? 0}</td>
                                    <td className="px-3 py-2 text-right">{stat.total_adults ?? 0}</td>
                                    <td className="px-3 py-2 text-right">{stat.total_seniors ?? 0}</td>
                                    <td className="px-3 py-2 text-right">{stat.total_pwd ?? 0}</td>
                                </tr>
                            ))}
                            {(!barangayStats || barangayStats.length === 0) && (
                                <tr>
                                    <td colSpan={9} className="px-4 py-8 text-center text-gray-500">
                                        No barangay data available. Add households to see statistics.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                        {barangayStats?.length > 0 && (
                            <tfoot className="bg-gray-50 font-semibold">
                                <tr>
                                    <td className="px-3 py-2">TOTAL</td>
                                    <td className="px-3 py-2 text-right">{barangayStats.reduce((sum, s) => sum + (s.households_count ?? 0), 0)}</td>
                                    <td className="px-3 py-2 text-right">{barangayStats.reduce((sum, s) => sum + (s.total_population ?? 0), 0)}</td>
                                    <td className="px-3 py-2 text-right">{barangayStats.reduce((sum, s) => sum + (s.total_males ?? 0), 0)}</td>
                                    <td className="px-3 py-2 text-right">{barangayStats.reduce((sum, s) => sum + (s.total_females ?? 0), 0)}</td>
                                    <td className="px-3 py-2 text-right">{barangayStats.reduce((sum, s) => sum + (s.total_children ?? 0), 0)}</td>
                                    <td className="px-3 py-2 text-right">{barangayStats.reduce((sum, s) => sum + (s.total_adults ?? 0), 0)}</td>
                                    <td className="px-3 py-2 text-right">{barangayStats.reduce((sum, s) => sum + (s.total_seniors ?? 0), 0)}</td>
                                    <td className="px-3 py-2 text-right">{barangayStats.reduce((sum, s) => sum + (s.total_pwd ?? 0), 0)}</td>
                                </tr>
                            </tfoot>
                        )}
                    </table>
                </div>
            </div>

            {/* Sitio Vulnerability Ranking */}
            <div className="bg-white rounded-lg shadow p-4 mb-8">
                <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 mb-4">
                    <h3 className="text-lg font-semibold">Sitio Vulnerability Ranking</h3>
                    <span className="text-sm text-gray-500">Highest percentage of vulnerable individuals</span>
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="px-4 py-2 text-left">Sitio</th>
                                <th className="px-4 py-2 text-right">Population</th>
                                <th className="px-4 py-2 text-right">Vulnerable (Total)</th>
                                <th className="px-4 py-2 text-right">PWDs</th>
                                <th className="px-4 py-2 text-right">Seniors</th>
                                <th className="px-4 py-2 text-right">Children</th>
                                <th className="px-4 py-2 text-right">Vulnerability Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            {sitioVulnerability.map((sitio, i) => (
                                <tr key={i} className={`border-b ${sitio.vulnerability_score > 50 ? 'bg-red-50' : (sitio.vulnerability_score > 30 ? 'bg-[#F7F9FB]' : '')}`}>
                                    <td className="px-4 py-2 font-medium max-w-48 break-words">{sitio.sitio}</td>
                                    <td className="px-4 py-2 text-right">{sitio.total_population}</td>
                                    <td className="px-4 py-2 text-right font-semibold">{sitio.vulnerable_count}</td>
                                    <td className="px-4 py-2 text-right">{sitio.pwd_count}</td>
                                    <td className="px-4 py-2 text-right">{sitio.senior_count}</td>
                                    <td className="px-4 py-2 text-right">{sitio.child_count}</td>
                                    <td className="px-4 py-2">
                                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2">
                                            <span className="font-bold tabular-nums">{sitio.vulnerability_score}%</span>
                                            <div className="w-24 bg-gray-200 rounded-full h-2 shrink-0">
                                                <div 
                                                    className={`h-2 rounded-full ${sitio.vulnerability_score > 50 ? 'bg-[#EF4444]' : (sitio.vulnerability_score > 30 ? 'bg-[#3B82F6]' : 'bg-[#000000]')}`}
                                                    style={{ width: `${Math.min(sitio.vulnerability_score, 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {sitioVulnerability.length === 0 && (
                                <tr>
                                    <td colSpan={7} className="px-4 py-4 text-center text-gray-500">
                                        No sitio data available
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Recent Households */}
            <div className="bg-white rounded-lg shadow p-4">
                <h3 className="text-lg font-semibold mb-4">Recently Added Households</h3>
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="px-4 py-2 text-left">Household Code</th>
                                <th className="px-4 py-2 text-left">Address</th>
                                <th className="px-4 py-2 text-left">Contact</th>
                                <th className="px-4 py-2 text-right">Members</th>
                                <th className="px-4 py-2 text-left">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            {recentHouseholds?.map((hh, i) => (
                                <tr key={i} className="border-b">
                                    <td className="px-4 py-2 font-medium">{hh.household_code}</td>
                                    <td className="px-4 py-2">
                                        {[hh.address?.street, hh.address?.purok_sitio, hh.address?.barangay?.name]
                                            .filter(Boolean)
                                            .join(', ')}
                                    </td>
                                    <td className="px-4 py-2">{hh.contact_number || '-'}</td>
                                    <td className="px-4 py-2 text-right">{hh.member_count ?? 0}</td>
                                    <td className="px-4 py-2">
                                        {hh.created_at ? new Date(hh.created_at).toLocaleDateString() : '-'}
                                    </td>
                                </tr>
                            ))}
                            {(!recentHouseholds || recentHouseholds.length === 0) && (
                                <tr>
                                    <td colSpan={5} className="px-4 py-4 text-center text-gray-500">
                                        No households yet
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </Layout>
    );
}
