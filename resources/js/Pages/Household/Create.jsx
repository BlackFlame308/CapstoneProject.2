import Layout from '@/Components/Layout';
import LocationCascader from '@/Components/LocationCascader';
import { useForm, Link } from '@inertiajs/react';
import { useState } from 'react';

// ✅ Defined OUTSIDE the component — fixes focus-loss bug
function FormInput({ label, name, type = 'text', value, onChange, error, ...props }) {
    return (
        <div className="mb-3">
            <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
            <input
                id={`field-${name}`}
                type={type}
                className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#3B82F6]"
                value={value}
                onChange={e => onChange(name, e.target.value)}
                {...props}
            />
            {error && <p className="text-red-500 text-xs mt-1">{error}</p>}
        </div>
    );
}

// Empty member template
const emptyMember = () => ({
    first_name: '',
    middle_name: '',
    last_name: '',
    birth_date: '',
    sex: 'M',
    relation: '',
    civil_status: '',
    education_level: '',
    occupation: '',
    is_pwd: false,
    is_pregnant: false,
});

export default function HouseholdCreate() {
    const { data, setData, post, processing, errors, transform } = useForm({
        household_name: '',
        email: '',
        street: '',
        purok_sitio: '',
        full_address: '',
        barangay_id: '',
        contact_number: '',
        emergency_contact: '',
        head_first_name: '',
        head_middle_name: '',
        head_last_name: '',
        head_birth_date: '',
        head_sex: 'M',
        head_relation: 'Head',
        head_civil_status: '',
        head_education_level: '',
        head_occupation: '',
        head_is_pwd: false,
        head_is_pregnant: false,
    });

    const [memberList, setMemberList] = useState([]);

    const addMember = () => {
        setMemberList([...memberList, emptyMember()]);
    };

    const updateMember = (index, field, value) => {
        const updated = [...memberList];
        updated[index] = { ...updated[index], [field]: value };
        setMemberList(updated);
    };

    const removeMember = (index) => {
        const updated = memberList.filter((_, i) => i !== index);
        setMemberList(updated);
    };

    const submit = (e) => {
        e.preventDefault();
        
        // Combine all data including members
        const formData = {
            ...data,
            members: memberList,
        };
        
        transform(() => formData);
        post('/households', {
            preserveScroll: true,
        });
    };

    const renderMemberFields = (members, listName) => (
        members.map((member, idx) => (
            <div key={`${listName}-${idx}`} className="border rounded p-3 mb-3 bg-gray-50">
                <div className="flex justify-between items-center mb-2">
                    <span className="font-medium text-sm text-gray-600">
                        Member {idx + 1}
                    </span>
                    <button
                        type="button"
                        onClick={() => removeMember(idx)}
                        className="text-red-500 text-sm hover:underline"
                    >
                        Remove
                    </button>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="First Name *"
                        value={member.first_name}
                        onChange={e => updateMember(idx, 'first_name', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Middle Name"
                        value={member.middle_name || ''}
                        onChange={e => updateMember(idx, 'middle_name', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Last Name *"
                        value={member.last_name}
                        onChange={e => updateMember(idx, 'last_name', e.target.value)}
                    />
                    <input
                        type="date"
                        className="border rounded px-2 py-1"
                        value={member.birth_date}
                        onChange={e => updateMember(idx, 'birth_date', e.target.value)}
                    />
                    <select
                        className="border rounded px-2 py-1"
                        value={member.sex || 'M'}
                        onChange={e => updateMember(idx, 'sex', e.target.value)}
                    >
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Relation to Head"
                        value={member.relation || ''}
                        onChange={e => updateMember(idx, 'relation', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Civil Status"
                        value={member.civil_status || ''}
                        onChange={e => updateMember(idx, 'civil_status', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Education"
                        value={member.education_level || ''}
                        onChange={e => updateMember(idx, 'education_level', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Occupation"
                        value={member.occupation || ''}
                        onChange={e => updateMember(idx, 'occupation', e.target.value)}
                    />
                    <div className="flex gap-4 items-center pl-2">
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={member.is_pwd || false}
                                onChange={e => updateMember(idx, 'is_pwd', e.target.checked)}
                            />
                            PWD
                        </label>
                        {(member.sex === 'F' || member.sex === 'female') && (
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    checked={member.is_pregnant || false}
                                    onChange={e => updateMember(idx, 'is_pregnant', e.target.checked)}
                                />
                                Pregnant
                            </label>
                        )}
                    </div>
                </div>
            </div>
        ))
    );

    return (
        <Layout title="Add Household">
            <form onSubmit={submit} className="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
                <h2 className="text-lg font-semibold mb-4">Household Information</h2>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <FormInput label="Household Name *" name="household_name" value={data.household_name} onChange={setData} error={errors.household_name} required />
                    <FormInput label="Login Email" name="email" type="email" value={data.email} onChange={setData} error={errors.email} />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormInput label="Street" name="street" value={data.street} onChange={setData} error={errors.street} />
                    <FormInput label="Purok / Sitio" name="purok_sitio" value={data.purok_sitio} onChange={setData} error={errors.purok_sitio} />
                    <FormInput label="Full Address (Optional)" name="full_address" value={data.full_address} onChange={setData} error={errors.full_address} />
                    <FormInput label="Contact Number" name="contact_number" value={data.contact_number} onChange={setData} error={errors.contact_number} />
                    <FormInput label="Emergency Contact" name="emergency_contact" value={data.emergency_contact} onChange={setData} error={errors.emergency_contact} />
                </div>

                <div className="mt-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <LocationCascader onChange={(val) => setData('barangay_id', val.barangay_id)} />
                    {errors.barangay_id && <p className="text-red-500 text-xs mt-1">{errors.barangay_id}</p>}
                </div>

                <h2 className="text-lg font-semibold mt-8 mb-4">Household Head</h2>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput label="First Name *" name="head_first_name" value={data.head_first_name} onChange={setData} error={errors.head_first_name} required />
                    <FormInput label="Middle Name" name="head_middle_name" value={data.head_middle_name} onChange={setData} error={errors.head_middle_name} />
                    <FormInput label="Last Name *" name="head_last_name" value={data.head_last_name} onChange={setData} error={errors.head_last_name} required />
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="mb-3">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Birth Date</label>
                        <input
                            type="date"
                            name="head_birth_date"
                            className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#3B82F6]"
                            value={data.head_birth_date}
                            onChange={e => setData('head_birth_date', e.target.value)}
                        />
                        {errors.head_birth_date && <p className="text-red-500 text-xs mt-1">{errors.head_birth_date}</p>}
                    </div>
                    <div className="mb-3">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Sex</label>
                        <select
                            name="head_sex"
                            className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#3B82F6]"
                            value={data.head_sex}
                            onChange={e => setData('head_sex', e.target.value)}
                        >
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                    <FormInput label="Civil Status" name="head_civil_status" value={data.head_civil_status} onChange={setData} />
                    <FormInput label="Education" name="head_education_level" value={data.head_education_level} onChange={setData} />
                    <FormInput label="Occupation" name="head_occupation" value={data.head_occupation} onChange={setData} />
                </div>
                <div className="flex flex-wrap gap-4 mt-2">
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={data.head_is_pwd}
                            onChange={e => setData('head_is_pwd', e.target.checked)}
                        />
                        PWD
                    </label>
                    {data.head_sex === 'F' && (
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={data.head_is_pregnant}
                                onChange={e => setData('head_is_pregnant', e.target.checked)}
                            />
                            Pregnant
                        </label>
                    )}
                </div>

                <h2 className="text-lg font-semibold mt-8 mb-4">Additional Members ({memberList.length})</h2>
                {renderMemberFields(memberList, 'new')}

                <button
                    type="button"
                    onClick={addMember}
                    className="bg-[#3B82F6] hover:bg-[#000000] text-white px-4 py-2 rounded transition mb-6"
                >
                    + Add Member
                </button>

                <div className="flex gap-3 mt-6 border-t pt-4">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-primary hover:bg-gray-800 text-white px-6 py-2 rounded transition disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Save Household'}
                    </button>
                    <Link href="/households" className="text-gray-600 hover:underline py-2">
                        Cancel
                    </Link>
                </div>
            </form>
        </Layout>
    );
}
