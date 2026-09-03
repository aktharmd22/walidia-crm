import { Head, router, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { ShieldCheck } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { PasswordInput } from '@/ui/Field'
import { Card, CardBody, CardHeader, CardTitle } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'

export default function Security({ two_factor_confirmed }: { two_factor_confirmed: boolean }) {
  const { data, setData, put, processing, errors, reset } = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
  })

  function submit(event: FormEvent) {
    event.preventDefault()
    put('/user/password', {
      preserveScroll: true,
      onSuccess: () => reset(),
    })
  }

  return (
    <>
      <Head title="Security" />

      <PageHeader title="Security" description="Password and two-factor authentication." />

      <div className="grid gap-5 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Change password</CardTitle>
          </CardHeader>
          <CardBody>
            <form onSubmit={submit} className="flex flex-col gap-3">
              <PasswordInput
                label="Current password"
                autoComplete="current-password"
                required
                value={data.current_password}
                error={errors.current_password}
                onChange={(event) => setData('current_password', event.target.value)}
              />
              <PasswordInput
                label="New password"
                autoComplete="new-password"
                required
                value={data.password}
                error={errors.password}
                help="At least 12 characters, mixed case and a number. Breached passwords are refused."
                onChange={(event) => setData('password', event.target.value)}
              />
              <PasswordInput
                label="Confirm new password"
                autoComplete="new-password"
                required
                value={data.password_confirmation}
                onChange={(event) => setData('password_confirmation', event.target.value)}
              />
              <div className="pt-2">
                <Button type="submit" variant="primary" loading={processing}>
                  Update password
                </Button>
              </div>
            </form>
          </CardBody>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Two-factor authentication</CardTitle>
            <StatusPill tone={two_factor_confirmed ? 'success' : 'neutral'}>
              {two_factor_confirmed ? 'Active' : 'Not set up'}
            </StatusPill>
          </CardHeader>
          <CardBody className="flex flex-col gap-3">
            <p className="text-body text-ink-soft">
              Recommended, not compulsory. If you lose your device, use a recovery code; if you have run out, an
              Admin can reset your enrolment.
            </p>
            <div>
              <Button
                variant="secondary"
                icon={<ShieldCheck className="size-4" />}
                onClick={() => router.visit('/two-factor/setup')}
              >
                {two_factor_confirmed ? 'Manage two-factor' : 'Set up two-factor'}
              </Button>
            </div>
          </CardBody>
        </Card>
      </div>
    </>
  )
}
