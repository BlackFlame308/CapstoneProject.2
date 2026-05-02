import Layout from '@/Components/Layout';
import { usePage, Link } from '@inertiajs/react';
import { PieChart, Pie, Cell, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const COLORS = ['#3B82F6', '#EF4444', '#93C5FD'];

export default function Dashboard() {
    const { stats, barangayStats, recentHouseholds, membersByBarangay, ageDistribution, flash } = usePage().props;
    const { auth } = usePage().props;
    // Safely access user with fallback to null if any property is undefined
    const user = auth?.user ?? null;
    const permissions = user?.permissions ?? {};

    const ageData = [
        { name: 'Children (0-17)', value: ageDistribution?.children ?? 0 },
        { name: 'Adults (18-59)', value: ageDistribution?.adults ?? 0 },
        { name: 'Seniors (60+)', value: ageDistribution?.seniors ?? 0 },
    ];

    const barangayData = membersByBarangay?.map(b => ({ name: b.name, population: b.count })) ?? [];

    const StatCard = ({ label, value, color = 'bg-gradient-brand' }) => (
        <div className="stat-card rounded-lg p-5">
            <div className="stat-card-number text-3xl">{value ?? 0}</div>
            <div className="stat-card-label text-sm mt-1">{label}</div>
        </div>
    );

    return (
        <Layout title="Dashboard">
            {flash?.success && (
                <div className="mb-4 p-3 bg-green-100 text-green-800 rounded">{flash.success}</div>
            )}
            {flash?.error && (
                <div className="mb-4 p-3 bg-red-100 text-red-800 rounded">{flash.error}</div>
            )}

            {/* Hero Section */}
            <div className="bg-hero text-white rounded-lg p-6 mb-8">
                <h2 className="text-3xl font-bold mb-2">Welcome to SafeTrack Dashboard</h2>
                <p className="text-lg opacity-90">Monitor and manage household data across barangays</p>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <StatCard label="Total Households" value={stats?.totalHouseholds} />
                <StatCard label="Total Population" value={stats?.totalMembers} />
                <StatCard label="PWD Count" value={stats?.totalPWD} />
                <StatCard label="Senior Citizens" value={stats?.totalSeniors} />
            </div>

{permissions.manage_accounts && (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <StatCard label="Total Users" value={stats?.totalUsers} color="from-gray-600 to-gray-800" />
                    <StatCard label="Captains" value={stats?.totalCaptains} color="from-gray-700 to-gray-900" />
                </div>
            )}

            {/* Charts */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div className="chart-card rounded-lg p-4">
                    <h3 className="text-lg font-semibold mb-4">Population Distribution by Age</h3>
                    <ResponsiveContainer width="100%" height={300}>
                        <PieChart>
                            <Pie data={ageData} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={100} label>
                                {ageData.map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                ))}
                            </Pie>
                            <Tooltip />
                            <Legend />
                        </PieChart>
                    </ResponsiveContainer>
                </div>

                <div className="chart-card rounded-lg p-4">
                    <h3 className="text-lg font-semibold mb-4">Population by Barangay</h3>
                    <ResponsiveContainer width="100%" height={300}>
                        <BarChart data={barangayData}>
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis dataKey="name" />
                            <YAxis />
                            <Tooltip />
                            <Bar dataKey="population" fill="#3B82F6" />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            </div>

            {/* Refresh Analytics */}
            <div className="chart-card rounded-lg p-4 mb-8">
                <h3 className="text-lg font-semibold mb-2">Update Analytics</h3>
                <p className="text-gray-600 text-sm mb-3">Refresh analytics data for all barangays based on current household and member data.</p>
                <Link
                    href="/analytics/update"
                    method="post"
                    as="button"
                    className="btn-primary px-4 py-2 rounded transition"
                >
                    Refresh Analytics
                </Link>
            </div>

            {/* Barangay Stats Table */}
            <div className="chart-card rounded-lg p-4 mb-8">
                <h3 className="text-lg font-semibold mb-4">Statistics by Barangay</h3>
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="px-4 py-2 text-left">Barangay</th>
                                <th className="px-4 py-2 text-left">Households</th>
                                <th className="px-4 py-2 text-left">Population</th>
                                <th className="px-4 py-2 text-left">PWD</th>
                                <th className="px-4 py-2 text-left">Seniors</th>
                            </tr>
                        </thead>
                        <tbody>
                            {barangayStats?.map((stat, i) => {
                                const analytics = stat.analytics?.[0];
                                return (
                                    <tr key={i} className="border-b">
                                        <td className="px-4 py-2">{stat.name}</td>
                                        <td className="px-4 py-2">{stat.addresses_count ?? 0}</td>
                                        <td className="px-4 py-2">{analytics?.total_population ?? 0}</td>
                                        <td className="px-4 py-2">{analytics?.total_pwd ?? 0}</td>
                                        <td className="px-4 py-2">{analytics?.total_seniors ?? 0}</td>
                                    </tr>
                                );
                            })}
                            {(!barangayStats || barangayStats.length === 0) && (
                                <tr><td colSpan="5" className="px-4 py-4 text-center text-gray-500">No barangays available</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Sitio Vulnerability Ranking */}
            <div className="bg-white rounded-lg shadow p-4 mb-8">
                <div className="flex justify-between items-center mb-4">
                    <h3 className="text-lg font-semibold">Sitio Vulnerability Ranking</h3>
                    <span className="text-sm text-gray-500">Highest percentage of vulnerable individuals</span>
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="px-4 py-2 text-left">Sitio</th>
                                <th className="px-4 py-2 text-left">Population</th>
                                <th className="px-4 py-2 text-left">Vulnerable (Total)</th>
                                <th className="px-4 py-2 text-left">PWDs</th>
                                <th className="px-4 py-2 text-left">Seniors</th>
                                <th className="px-4 py-2 text-left">Children</th>
                                <th className="px-4 py-2 text-left">Vulnerability Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            {usePage().props.sitioVulnerability?.map((sitio, i) => (
                                <tr key={i} className={`border-b ${sitio.vulnerability_score > 50 ? 'bg-red-50' : (sitio.vulnerability_score > 30 ? 'bg-orange-50' : '')}`}>
                                    <td className="px-4 py-2 font-medium">{sitio.sitio}</td>
                                    <td className="px-4 py-2">{sitio.total_population}</td>
                                    <td className="px-4 py-2 font-semibold">{sitio.vulnerable_count}</td>
                                    <td className="px-4 py-2">{sitio.pwd_count}</td>
                                    <td className="px-4 py-2">{sitio.senior_count}</td>
                                    <td className="px-4 py-2">{sitio.child_count}</td>
                                    <td className="px-4 py-2">
                                        <div className="flex items-center">
                                            <span className="mr-2 font-bold">{sitio.vulnerability_score}%</span>
                                            <div className="w-24 bg-gray-200 rounded-full h-2">
                                                <div 
                                                    className={`h-2 rounded-full ${sitio.vulnerability_score > 50 ? 'bg-red-600' : (sitio.vulnerability_score > 30 ? 'bg-orange-500' : 'bg-green-500')}`} 
                                                    style={{ width: `${Math.min(sitio.vulnerability_score, 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {(!usePage().props.sitioVulnerability || usePage().props.sitioVulnerability.length === 0) && (
                                <tr><td colSpan="7" className="px-4 py-4 text-center text-gray-500">No sitio data available</td></tr>
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
                                <th className="px-4 py-2 text-left">Members</th>
                                <th className="px-4 py-2 text-left">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            {recentHouseholds?.map((hh, i) => (
                                <tr key={i} className="border-b">
                                    <td className="px-4 py-2">{hh.household_code}</td>
                                    <td className="px-4 py-2">
                                        {hh.address?.street} {hh.address?.purok},
                                        {hh.address?.sitio ? hh.address.sitio.name + ', ' : ''}
                                        {hh.address?.barangay?.name}
                                    </td>
                                    <td className="px-4 py-2">{hh.contact_number}</td>
                                    <td className="px-4 py-2">{hh.population ?? 0}</td>
                                    <td className="px-4 py-2">{hh.created_at ? new Date(hh.created_at).toLocaleDateString() : '-'}</td>
                                </tr>
                            ))}
                            {(!recentHouseholds || recentHouseholds.length === 0) && (
                                <tr><td colSpan="5" className="px-4 py-4 text-center text-gray-500">No households yet</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </Layout>
    );
}

