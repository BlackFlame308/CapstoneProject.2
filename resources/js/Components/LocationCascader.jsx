import { useState, useEffect } from 'react';
import axios from 'axios';

export default function LocationCascader({ onChange, defaultLocationId }) {
    const [regions, setRegions] = useState([]);
    const [provinces, setProvinces] = useState([]);
    const [cities, setCities] = useState([]);
    const [barangays, setBarangays] = useState([]);
    const [sitios, setSitios] = useState([]);

    const [selectedRegion, setSelectedRegion] = useState('');
    const [selectedProvince, setSelectedProvince] = useState('');
    const [selectedCity, setSelectedCity] = useState('');
    const [selectedBarangay, setSelectedBarangay] = useState('');
    const [selectedSitio, setSelectedSitio] = useState('');

    useEffect(() => {
        axios.get('/locations/regions').then(r => setRegions(r.data.data));
    }, []);

    useEffect(() => {
        if (!selectedRegion) return setProvinces([]);
        axios.get(`/locations/provinces/${selectedRegion}`).then(r => setProvinces(r.data.data));
    }, [selectedRegion]);

    useEffect(() => {
        if (!selectedProvince) return setCities([]);
        axios.get(`/locations/cities/${selectedProvince}`).then(r => setCities(r.data.data));
    }, [selectedProvince]);

    useEffect(() => {
        if (!selectedCity) return setBarangays([]);
        axios.get(`/locations/barangays/${selectedCity}`).then(r => setBarangays(r.data.data));
    }, [selectedCity]);

    useEffect(() => {
        if (!selectedBarangay) return setSitios([]);
        axios.get(`/locations/sitios/${selectedBarangay}`).then(r => setSitios(r.data.data));
    }, [selectedBarangay]);

    const emit = (barangayId, sitioId) => {
        if (onChange) {
            onChange({
                barangay_id: barangayId,
                sitio_id: sitioId || null
            });
        }
    };

    const Select = ({ label, options, value, onSelect }) => (
        <div className="mb-3">
            <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
            <select
                className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#3B82F6]"
                value={value}
                onChange={e => onSelect(e.target.value)}
            >
                <option value="">-- Select {label} --</option>
                {options.map(o => (
                    <option key={o.id} value={o.id}>{o.name}</option>
                ))}
            </select>
        </div>
    );

    return (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <Select label="Region" options={regions} value={selectedRegion} onSelect={v => {
                setSelectedRegion(v);
                setSelectedProvince(''); setSelectedCity(''); setSelectedBarangay(''); setSelectedSitio('');
                emit('', '');
            }} />
            <Select label="Province" options={provinces} value={selectedProvince} onSelect={v => {
                setSelectedProvince(v);
                setSelectedCity(''); setSelectedBarangay(''); setSelectedSitio('');
                emit('', '');
            }} />
            <Select label="City" options={cities} value={selectedCity} onSelect={v => {
                setSelectedCity(v);
                setSelectedBarangay(''); setSelectedSitio('');
                emit('', '');
            }} />
            <Select label="Barangay" options={barangays} value={selectedBarangay} onSelect={v => {
                setSelectedBarangay(v);
                setSelectedSitio('');
                emit(v, '');
            }} />
            {sitios.length > 0 && (
                <Select label="Sitio" options={sitios} value={selectedSitio} onSelect={v => {
                    setSelectedSitio(v);
                    emit(selectedBarangay, v);
                }} />
            )}
        </div>
    );
}

