import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { incidentSections } from '@/pages/Charter/Incidents/Create'

interface Option {
  value: string | number
  label: string
}

export default function IncidentEdit({
  record,
  bookings = [],
  yachts = [],
}: {
  record: Record<string, unknown> & { id: number; reference: string | null }
  bookings?: Option[]
  yachts?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.reference ?? 'incident'}`}
      description="Corrections are audited: the original wording stays in the record."
      sections={incidentSections(bookings, yachts)}
      initial={{
        booking_id: fv(record.booking_id),
        yacht_id: fv(record.yacht_id),
        type: fv(record.type, 'other'),
        severity: fv(record.severity, 'minor'),
        occurred_at: String(fv(record.occurred_at)).slice(0, 16),
        description: fv(record.description),
        immediate_action: fv(record.immediate_action),
        injuries: fv(record.injuries, false),
        authorities_notified: fv(record.authorities_notified, false),
        insurance_claim_ref: fv(record.insurance_claim_ref),
      }}
      action={`/charter/incidents/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/charter/incidents/${record.id}`}
    />
  )
}
