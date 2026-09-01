import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { yachtSections } from '@/pages/Fleet/Create'

interface Option {
  value: string | number
  label: string
}

export default function YachtEdit({
  record,
  marinas = [],
}: {
  record: Record<string, unknown> & { id: number; name: string }
  marinas?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.name}`}
      sections={yachtSections(marinas)}
      initial={{
        name: fv(record.name),
        name_ar: fv(record.name_ar),
        builder: fv(record.builder),
        model: fv(record.model),
        year_built: fv(record.year_built),
        year_refit: fv(record.year_refit),
        status: fv(record.status, 'active'),
        home_marina_id: (record.home_marina as { id: number } | null)?.id ?? '',
        is_charter_fleet: Boolean(record.is_charter_fleet),
        is_for_sale: Boolean(record.is_for_sale),
        is_managed: Boolean(record.is_managed),
        loa_m: fv(record.loa_m),
        beam_m: fv(record.beam_m),
        draft_m: fv(record.draft_m),
        gross_tonnage: fv(record.gross_tonnage),
        engines: fv(record.engines),
        engine_hours: fv(record.engine_hours),
        cruising_speed_kn: fv(record.cruising_speed_kn),
        max_speed_kn: fv(record.max_speed_kn),
        capacity_static: fv(record.capacity_static),
        capacity_cruising: fv(record.capacity_cruising),
        cabins: fv(record.cabins),
        berths: fv(record.berths),
        crew_count: fv(record.crew_count),
        flag_country: fv(record.flag_country),
        registration_no: fv(record.registration_no),
        imo_no: fv(record.imo_no),
        mmsi: fv(record.mmsi),
        description: fv(record.description),
      }}
      action={`/fleet/yachts/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/fleet/yachts/${record.id}`}
    />
  )
}
