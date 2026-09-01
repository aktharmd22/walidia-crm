import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function yachtSections(marinas: Option[]): FormSection[] {
  return [
    {
      title: 'Identity',
      fields: [
        { name: 'name', label: 'Yacht name', required: true },
        { name: 'name_ar', label: 'Name in Arabic' },
        { name: 'builder', label: 'Builder' },
        { name: 'model', label: 'Model' },
        { name: 'year_built', label: 'Year built', type: 'number' },
        { name: 'year_refit', label: 'Year refit', type: 'number' },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: [
            { value: 'active', label: 'Active' },
            { value: 'maintenance', label: 'In maintenance' },
            { value: 'off_market', label: 'Off market' },
            { value: 'sold', label: 'Sold' },
            { value: 'archived', label: 'Archived' },
          ],
        },
        { name: 'home_marina_id', label: 'Home marina', type: 'select', options: marinas },
      ],
    },
    {
      title: 'What it is used for',
      description: 'One hull can carry all three at once — the commercial terms live on separate profiles.',
      fields: [
        { name: 'is_charter_fleet', label: 'In the charter fleet', type: 'checkbox' },
        { name: 'is_for_sale', label: 'Listed for sale', type: 'checkbox' },
        { name: 'is_managed', label: 'Under management', type: 'checkbox' },
      ],
    },
    {
      title: 'Dimensions and machinery',
      fields: [
        { name: 'loa_m', label: 'LOA (m)', type: 'number' },
        { name: 'beam_m', label: 'Beam (m)', type: 'number' },
        { name: 'draft_m', label: 'Draft (m)', type: 'number' },
        { name: 'gross_tonnage', label: 'Gross tonnage', type: 'number' },
        { name: 'engines', label: 'Engines' },
        { name: 'engine_hours', label: 'Engine hours', type: 'number' },
        { name: 'cruising_speed_kn', label: 'Cruising speed (kn)', type: 'number' },
        { name: 'max_speed_kn', label: 'Max speed (kn)', type: 'number' },
      ],
    },
    {
      title: 'Capacity',
      description: 'Cruising capacity is a licensing limit and can never exceed static capacity.',
      fields: [
        { name: 'capacity_static', label: 'Static capacity', type: 'number' },
        { name: 'capacity_cruising', label: 'Cruising capacity', type: 'number' },
        { name: 'cabins', label: 'Cabins', type: 'number' },
        { name: 'berths', label: 'Berths', type: 'number' },
        { name: 'crew_count', label: 'Crew', type: 'number' },
      ],
    },
    {
      title: 'Registration',
      fields: [
        { name: 'flag_country', label: 'Flag' },
        { name: 'registration_no', label: 'Registration number' },
        { name: 'imo_no', label: 'IMO number' },
        { name: 'mmsi', label: 'MMSI' },
        { name: 'description', label: 'Description', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function YachtCreate({ marinas = [] }: { marinas?: Option[] }) {
  return (
    <ResourceForm
      title="Add a yacht"
      description="Specs live here once. Charter rates, asking price and management terms hang off this record."
      sections={yachtSections(marinas)}
      initial={{
        name: '',
        name_ar: '',
        builder: '',
        model: '',
        year_built: '',
        year_refit: '',
        status: 'active',
        home_marina_id: '',
        is_charter_fleet: true,
        is_for_sale: false,
        is_managed: false,
        loa_m: '',
        beam_m: '',
        draft_m: '',
        gross_tonnage: '',
        engines: '',
        engine_hours: '',
        cruising_speed_kn: '',
        max_speed_kn: '',
        capacity_static: '',
        capacity_cruising: '',
        cabins: '',
        berths: '',
        crew_count: '',
        flag_country: '',
        registration_no: '',
        imo_no: '',
        mmsi: '',
        description: '',
      }}
      action="/fleet/yachts"
      submitLabel="Add yacht"
      cancelUrl="/fleet/yachts"
    />
  )
}
