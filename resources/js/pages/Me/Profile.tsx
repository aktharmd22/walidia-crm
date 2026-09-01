import { Head, router, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { Palette, ShieldCheck } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Input, Select } from '@/ui/Field'
import { Card, CardBody, CardHeader, CardTitle, DateText } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'

interface Props {
  profile: {
    name: string
    email: string
    phone: string | null
    job_title: string | null
    locale: 'en' | 'ar'
    chrome: 'navy' | 'light'
    accent: 'brass' | 'blue'
    avatar_url: string | null
  }
  security: {
    two_factor_confirmed: boolean
    last_login_at: string | null
    last_login_ip: string | null
  }
}

export default function Profile({ profile, security }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    name: profile.name,
    email: profile.email,
    phone: profile.phone ?? '',
    job_title: profile.job_title ?? '',
    locale: profile.locale,
  })

  function submit(event: FormEvent) {
    event.preventDefault()
    put('/me/profile')
  }

  return (
    <>
      <Head title="Profile" />

      <PageHeader title="Profile" description="Your details, appearance and security." />

      <div className="grid gap-5 lg:grid-cols-[1.3fr_1fr]">
        <Card>
          <CardHeader>
            <CardTitle>Details</CardTitle>
          </CardHeader>
          <CardBody>
            <form onSubmit={submit} className="flex flex-col gap-3">
              <Input
                label="Name"
                required
                value={data.name}
                error={errors.name}
                onChange={(event) => setData('name', event.target.value)}
              />
              <Input
                label="Email"
                type="email"
                required
                value={data.email}
                error={errors.email}
                onChange={(event) => setData('email', event.target.value)}
              />
              <Input
                label="Mobile"
                value={data.phone}
                error={errors.phone}
                help="E.164 format, e.g. +971 50 123 4567"
                onChange={(event) => setData('phone', event.target.value)}
              />
              <Input
                label="Job title"
                value={data.job_title}
                error={errors.job_title}
                onChange={(event) => setData('job_title', event.target.value)}
              />
              <Select
                label="Language"
                value={data.locale}
                error={errors.locale}
                onChange={(event) => setData('locale', event.target.value as 'en' | 'ar')}
                options={[
                  { value: 'en', label: 'English' },
                  { value: 'ar', label: 'العربية' },
                ]}
              />
              <div className="pt-2">
                <Button type="submit" variant="primary" loading={processing}>
                  Save changes
                </Button>
              </div>
            </form>
          </CardBody>
        </Card>

        <div className="flex flex-col gap-5">
          <Card>
            <CardHeader>
              <CardTitle>Appearance</CardTitle>
            </CardHeader>
            <CardBody className="flex flex-col gap-4">
              <div className="flex items-center justify-between gap-3">
                <span>
                  <span className="block text-h3 text-ink">Sidebar</span>
                  <span className="block text-small text-ink-faint">
                    {profile.chrome === 'navy' ? 'Deep navy — the brand default' : 'Light — matches the reference UI'}
                  </span>
                </span>
                <Button
                  variant="secondary"
                  size="sm"
                  icon={<Palette className="size-4" />}
                  onClick={() => router.post(`/me/chrome/${profile.chrome === 'navy' ? 'light' : 'navy'}`)}
                >
                  Switch
                </Button>
              </div>

              <div className="flex items-center justify-between gap-3">
                <span>
                  <span className="block text-h3 text-ink">Accent</span>
                  <span className="block text-small text-ink-faint">
                    {profile.accent === 'brass' ? 'Brass' : 'Blue'}
                  </span>
                </span>
                <Button
                  variant="secondary"
                  size="sm"
                  onClick={() => router.post(`/me/accent/${profile.accent === 'brass' ? 'blue' : 'brass'}`)}
                >
                  Switch
                </Button>
              </div>
            </CardBody>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Security</CardTitle>
              <StatusPill tone={security.two_factor_confirmed ? 'success' : 'danger'}>
                {security.two_factor_confirmed ? '2FA active' : '2FA missing'}
              </StatusPill>
            </CardHeader>
            <CardBody className="flex flex-col gap-3">
              <p className="text-body text-ink-soft">
                Two-factor authentication is required on every Walidia account. Access to VIP records and guest
                manifests is logged against your name.
              </p>
              <dl className="flex flex-col gap-2 text-small">
                <div className="flex justify-between gap-3">
                  <dt className="text-ink-faint">Last sign-in</dt>
                  <dd>
                    <DateText value={security.last_login_at} withTime />
                  </dd>
                </div>
                <div className="flex justify-between gap-3">
                  <dt className="text-ink-faint">From</dt>
                  <dd className="numeric text-ink-soft">{security.last_login_ip ?? '—'}</dd>
                </div>
              </dl>
              <div className="flex flex-wrap gap-3 pt-1">
                <Button
                  variant="secondary"
                  icon={<ShieldCheck className="size-4" />}
                  onClick={() => router.visit('/me/sessions')}
                >
                  Active sessions
                </Button>
                <Button variant="ghost" onClick={() => router.visit('/two-factor/setup')}>
                  Two-factor settings
                </Button>
              </div>
            </CardBody>
          </Card>
        </div>
      </div>
    </>
  )
}
