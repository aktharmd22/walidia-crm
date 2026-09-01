import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function crewSections(marinas: Option[]): FormSection[] {
  return [
    {
      title: 'The person',
      fields: [
        { name: 'first_name', label: 'First name', required: true },
        { name: 'last_name', label: 'Last name' },
        {
          name: 'role',
          label: 'Role',
          type: 'select',
          required: true,
          options: [
            { value: 'captain', label: 'Captain' },
            { value: 'engineer', label: 'Engineer' },
            { value: 'deckhand', label: 'Deckhand' },
            { value: 'steward', label: 'Steward / stewardess' },
            { value: 'chef', label: 'Chef' },
            { value: 'other', label: 'Other' },
          ],
        },
        {
          name: 'employment_type',
          label: 'Employment',
          type: 'select',
          required: true,
          help: 'Payroll is out of scope; per-charter payouts and tips are not.',
          options: [
            { value: 'employee', label: 'Employee' },
            { value: 'freelance', label: 'Freelance' },
          ],
        },
        { name: 'nationality', label: 'Nationality' },
        { name: 'mobile', label: 'Mobile', type: 'tel' },
        { name: 'email', label: 'Email', type: 'email' },
        { name: 'day_rate', label: 'Day rate', type: 'money' },
        { name: 'home_marina_id', label: 'Home marina', type: 'select', options: marinas },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: [
            { value: 'active', label: 'Active' },
            { value: 'on_leave', label: 'On leave' },
            { value: 'inactive', label: 'Inactive' },
          ],
        },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function CrewCreate({ marinas = [] }: { marinas?: Option[] }) {
  return (
    <ResourceForm
      title="Add crew"
      description="Documents and their expiry dates are added on the crew member's own screen."
      sections={crewSections(marinas)}
      initial={{
        first_name: '',
        last_name: '',
        role: 'deckhand',
        employment_type: 'freelance',
        nationality: '',
        mobile: '',
        email: '',
        day_rate: '',
        currency: 'AED',
        home_marina_id: '',
        status: 'active',
        notes: '',
      }}
      action="/crew"
      submitLabel="Add crew member"
      cancelUrl="/crew"
    />
  )
}
