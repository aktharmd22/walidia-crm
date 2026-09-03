import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { yachts?: Option[]; listings?: Option[] }): FormSection[] {
  return [
    {
      title: 'The valuation',
      description: 'The comparables matter as much as the number — they are what makes the price defensible.',
      fields: [
        { name: 'yacht_id', label: 'Yacht', type: 'select', required: true, options: props.yachts ?? [] },
        { name: 'listing_id', label: 'Listing', type: 'select', options: props.listings ?? [] },
        { name: 'valued_on', label: 'Valued on', type: 'date', required: true },
        { name: 'market_low', label: 'Market low', type: 'money' },
        { name: 'market_high', label: 'Market high', type: 'money' },
        { name: 'broker_valuation', label: 'Broker valuation', type: 'money', required: true },
        { name: 'recommended_asking', label: 'Recommended asking', type: 'money' },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['EUR', 'USD', 'AED', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['draft', 'issued', 'accepted'].map((value) => ({ value, label: value })),
        },
        { name: 'rationale', label: 'How we arrived at it', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { yachts?: Option[]; listings?: Option[] }) {
  return (
    <ResourceForm
      title="Value a yacht"
      description="What a yacht is worth and the working behind it — an asking price a broker can defend."
      sections={sections(props)}
      initial={{
        yacht_id: '',
        listing_id: '',
        valued_on: new Date().toISOString().slice(0, 10),
        market_low: '',
        market_high: '',
        broker_valuation: '',
        recommended_asking: '',
        currency: 'EUR',
        status: 'draft',
        rationale: '',
      }}
      action="/brokerage/valuations"
      submitLabel="Save"
      cancelUrl="/brokerage/valuations"
    />
  )
}
