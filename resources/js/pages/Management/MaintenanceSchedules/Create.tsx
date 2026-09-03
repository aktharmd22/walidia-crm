import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { yachts?: Option[]; vendors?: Option[] }): FormSection[] {
  return [
    {
      title: 'The schedule',
      description: 'Set a calendar interval, engine hours, or both — whichever falls first is what comes due.',
      fields: [
        { name: 'yacht_id', label: 'Yacht', type: 'select', required: true, options: props.yachts ?? [] },
        {
          name: 'system',
          label: 'System',
          type: 'select',
          required: true,
          options: ['engines', 'generator', 'air_conditioning', 'electrical', 'plumbing', 'hull', 'cleaning'].map(
            (value) => ({ value, label: value.replace(/_/g, ' ') }),
          ),
        },
        { name: 'title', label: 'Work', required: true, wide: true },
        { name: 'interval_days', label: 'Every (days)', type: 'number' },
        { name: 'interval_engine_hours', label: 'Every (engine hours)', type: 'number' },
        { name: 'last_done_on', label: 'Last done', type: 'date' },
        { name: 'next_due_on', label: 'Next due', type: 'date' },
        { name: 'vendor_id', label: 'Vendor', type: 'select', options: props.vendors ?? [] },
        { name: 'budget_cost', label: 'Budget', type: 'money' },
        { name: 'blocks_charter', label: 'Overdue work blocks charter', type: 'checkbox', wide: true },
        { name: 'is_active', label: 'Active', type: 'checkbox', wide: true },
        { name: 'description', label: 'Detail', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { yachts?: Option[]; vendors?: Option[] }) {
  return (
    <ResourceForm
      title="Add a schedule"
      description="Preventive work that recurs — whichever comes first, the calendar or the engine hours."
      sections={sections(props)}
      initial={{
        yacht_id: '',
        system: 'engines',
        title: '',
        interval_days: 180,
        interval_engine_hours: '',
        last_done_on: '',
        next_due_on: '',
        vendor_id: '',
        budget_cost: '',
        blocks_charter: false,
        is_active: true,
        description: '',
      }}
      action="/management/maintenance-schedules"
      submitLabel="Save"
      cancelUrl="/management/maintenance-schedules"
    />
  )
}
