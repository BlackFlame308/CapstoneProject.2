import Layout from '@/Components/Layout';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function CSVUpload() {
    const { data, setData, post, processing, errors, progress } = useForm({
        csv_file: null,
    });

    const [dragActive, setDragActive] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        post('/csv/upload', {
            forceFormData: true,
        });
    };

    const handleDrag = (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === 'dragenter' || e.type === 'dragover') {
            setDragActive(true);
        } else if (e.type === 'dragleave') {
            setDragActive(false);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            setData('csv_file', e.dataTransfer.files[0]);
        }
    };

    return (
        <Layout title="Upload CSV">
            <div className="max-w-2xl mx-auto">
                <div className="bg-white rounded-lg shadow p-6">
                    <h2 className="text-lg font-semibold mb-2">Import Households from CSV</h2>
                    <p className="text-gray-600 text-sm mb-6">
                        Upload a CSV file containing household and member data.
                    </p>

                    <form onSubmit={submit} className="space-y-4">
                        <div
                            className={`border-2 border-dashed rounded-lg p-8 text-center transition ${
                                dragActive
                                    ? 'border-indigo-500 bg-indigo-50'
                                    : 'border-gray-300 hover:border-gray-400'
                            }`}
                            onDragEnter={handleDrag}
                            onDragLeave={handleDrag}
                            onDragOver={handleDrag}
                            onDrop={handleDrop}
                        >
                            <input
                                type="file"
                                accept=".csv,.txt"
                                onChange={(e) => setData('csv_file', e.target.files[0])}
                                className="hidden"
                                id="csv-upload"
                            />
                            <label htmlFor="csv-upload" className="cursor-pointer block">
                                <div className="text-gray-500 mb-2">
                                    {data.csv_file ? (
                                        <span className="text-indigo-600 font-medium">{data.csv_file.name}</span>
                                    ) : (
                                        <>
                                            <span className="block text-3xl mb-2">&#128206;</span>
                                            <span>Drag and drop your CSV file here, or click to browse</span>
                                        </>
                                    )}
                                </div>
                                <span className="text-xs text-gray-400">Supported formats: .csv, .txt (max 10MB)</span>
                            </label>
                        </div>

                        {errors.csv_file && (
                            <p className="text-red-500 text-sm">{errors.csv_file}</p>
                        )}

                        {progress && (
                            <div className="w-full bg-gray-200 rounded-full h-2.5">
                                <div
                                    className="bg-indigo-600 h-2.5 rounded-full transition-all"
                                    style={{ width: `${progress.percentage}%` }}
                                />
                            </div>
                        )}

                        <div className="flex gap-3">
                            <button
                                type="submit"
                                disabled={processing || !data.csv_file}
                                className="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded transition disabled:opacity-50"
                            >
                                {processing ? 'Uploading...' : 'Upload & Import'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Layout>
    );
}
