import Layout from '@/Components/Layout';
import LocationCascader from '@/Components/LocationCascader';
import { useForm, Link } from '@inertiajs/react';
import { useState } from 'react';

// ✅ Defined OUTSIDE component — prevents focus-loss on keystroke
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

export default function HouseholdEdit({ household }) {
    const { data, setData, put, processing, errors, transform } = useForm({
        household_name: household?.household_name || '',
        email: household?.email || '',
        street: household?.address?.street || '',
        purok_sitio: household?.address?.purok_sitio || '',
        full_address: household?.address?.full_address || '',
        barangay_id: household?.address?.barangay_id || '',
        contact_number: household?.contact_number || '',
        emergency_contact: household?.emergency_contact || '',
    });

    const [memberList, setMemberList] = useState(
        household?.members?.length > 0
            ? household.members.map(member => ({
                ...member,
                birth_date: member.birth_date ? String(member.birth_date).slice(0, 10) : '',
            }))
            : []
    );
    const [newMembers, setNewMembers] = useState([]);

    const updateMember = (listName, index, field, value) => {
        const updated = [...(listName === 'existing' ? memberList : newMembers)];
        updated[index][field] = value;
        if (listName === 'existing') {
            setMemberList(updated);
        } else {
            setNewMembers(updated);
        }
    };

    const addMember = () => {
        const updated = [...newMembers, emptyMember()];
        setNewMembers(updated);
    };

    const removeNewMember = (index) => {
        const updated = newMembers.filter((_, i) => i !== index);
        setNewMembers(updated);
    };

    const submit = (e) => {
        e.preventDefault();
        
        const allMembers = [...memberList, ...newMembers];

        transform(() => ({ ...data, members: allMembers }));
        put(`/households/${household.id}`, {
            preserveScroll: true,
        });
    };

    const renderMemberFields = (members, listName) => (
        members.map((member, idx) => (
            <div key={`${listName}-${idx}`} className="border rounded p-3 mb-3 bg-gray-50">
                <div className="flex justify-between items-center mb-2">
                    <span className="font-medium text-sm text-gray-600">
                        {listName === 'existing' ? `Member ${idx + 1}` : `New Member ${idx + 1}`}
                    </span>
                    {listName === 'new' && (
                        <button
                            type="button"
                            onClick={() => removeNewMember(idx)}
                            className="text-red-500 text-sm hover:underline"
                        >
                            Remove
                        </button>
                    )}
                </div>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="First Name *"
                        value={member.first_name}
                        onChange={e => updateMember(listName, idx, 'first_name', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Middle Name"
                        value={member.middle_name || ''}
                        onChange={e => updateMember(listName, idx, 'middle_name', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Last Name *"
                        value={member.last_name}
                        onChange={e => updateMember(listName, idx, 'last_name', e.target.value)}
                    />
                    <input
                        type="date"
                        className="border rounded px-2 py-1"
                        value={member.birth_date}
                        onChange={e => updateMember(listName, idx, 'birth_date', e.target.value)}
                    />
                    <select
                        className="border rounded px-2 py-1"
                        value={member.sex || 'M'}
                        onChange={e => updateMember(listName, idx, 'sex', e.target.value)}
                    >
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Relation to Head"
                        value={member.relation || ''}
                        onChange={e => updateMember(listName, idx, 'relation', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Civil Status"
                        value={member.civil_status || ''}
                        onChange={e => updateMember(listName, idx, 'civil_status', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Education"
                        value={member.education_level || ''}
                        onChange={e => updateMember(listName, idx, 'education_level', e.target.value)}
                    />
                    <input
                        className="border rounded px-2 py-1"
                        placeholder="Occupation"
                        value={member.occupation || ''}
                        onChange={e => updateMember(listName, idx, 'occupation', e.target.value)}
                    />
                    <div className="flex gap-4 items-center pl-2">
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={member.is_pwd || false}
                                onChange={e => updateMember(listName, idx, 'is_pwd', e.target.checked)}
                            />
                            PWD
                        </label>
                        {(member.sex === 'F' || member.sex === 'female') && (
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    checked={member.is_pregnant || false}
                                    onChange={e => updateMember(listName, idx, 'is_pregnant', e.target.checked)}
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
        <Layout title={`Edit Household ${household?.household_code}`}>
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
                    <LocationCascader
                        defaultLocationId={household?.address?.barangay_id}
                        onChange={(val) => setData('barangay_id', val.barangay_id)}
                    />
                    {errors.barangay_id && <p className="text-red-500 text-xs mt-1">{errors.barangay_id}</p>}
                </div>

                <h2 className="text-lg font-semibold mt-8 mb-4">Members ({memberList.length + newMembers.length})</h2>
                
                {renderMemberFields(memberList, 'existing')}
                {renderMemberFields(newMembers, 'new')}

                <div className="flex gap-3 mt-4 mb-6">
                    <button
                        type="button"
                        onClick={addMember}
                        className="bg-[#3B82F6] hover:bg-[#000000] text-white px-4 py-2 rounded transition"
                    >
                        + Add Member
                    </button>
                </div>

                <div className="flex gap-3 mt-6 border-t pt-4">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-primary hover:bg-gray-800 text-white px-6 py-2 rounded transition disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Update Household'}
                    </button>
                    <Link href={`/households/${household.id}`} className="text-gray-600 hover:underline py-2">
                        Cancel
                    </Link>
                </div>
            </form>
        </Layout>
    );
}
