import { Head, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { Button } from '@/ui/Button'
import { Input } from '@/ui/Field'

export default function ResetPassword({ email, token }: { email: string; token: string }) {
  const { data, setData, post, processing, errors } = useForm({
    token,
    email,
    password: '',
    password_confirmation: '',
  })

  function submit(event: FormEvent) {
    event.preventDefault()
    post('/reset-password')
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-deck px-4 py-12">
      <Head title="Choose a new password" />

      <div className="w-full max-w-[400px] rounded-card border border-line bg-hull p-6">
        <h1 className="text-h1 text-ink">Choose a new password</h1>
        <p className="mt-2 text-body text-ink-soft">
          At least 12 characters, with upper and lower case and a number. Passwords found in known breaches are
          refused.
        </p>

        <form onSubmit={submit} className="mt-6 flex flex-col gap-3">
          <Input
            label="Email"
            type="email"
            required
            value={data.email}
            error={errors.email}
            onChange={(event) => setData('email', event.target.value)}
          />
          <Input
            label="New password"
            type="password"
            autoComplete="new-password"
            required
            autoFocus
            value={data.password}
            error={errors.password}
            onChange={(event) => setData('password', event.target.value)}
          />
          <Input
            label="Confirm new password"
            type="password"
            autoComplete="new-password"
            required
            value={data.password_confirmation}
            onChange={(event) => setData('password_confirmation', event.target.value)}
          />
          <Button type="submit" variant="primary" size="lg" block loading={processing} className="mt-3">
            Set password
          </Button>
        </form>
      </div>
    </div>
  )
}

ResetPassword.layout = undefined
