import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { crewSections } from '@/pages/Crew/Create'

interface Option {
  value: string | number
  label: string
}

export default function CrewEdit({
  record,
  marinas = [],
}: {
  record: Record<string, unknown> & { id: number; full_name: string }
  marinas?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.full_name}`}
      sections={crewSections(marinas)}
      initial={{
        first_name: fv(record.first_name),
        last_name: fv(record.last_name),
        role: fv(record.role, 'deckhand'),
        employment_type: fv(record.employment_type, 'freelance'),
        nationality: fv(record.nationality),
        mobile: fv(record.mobile),
        email: fv(record.email),
        day_rate: fv(record.day_rate),
        currency: fv(record.currency, 'AED'),
        home_marina_id: fv(record.home_marina_id),
        status: fv(record.status, 'active'),
        notes: fv(record.notes),
      }}
      action={`/crew/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/crew/${record.id}`}
    />
  )
}
