import { useForm, Head } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { Button } from '@/ui/Button'
import { Checkbox, Input, PasswordInput } from '@/ui/Field'

export default function Login({ status, canResetPassword }: { status?: string; canResetPassword?: boolean }) {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false as boolean,
  })

  function submit(event: FormEvent) {
    event.preventDefault()
    post('/login')
  }

  return (
    <div className="grid min-h-screen lg:grid-cols-2">
      <Head title="Sign in" />

      {/*
       * Dubai Marina at night — public domain, from Wikimedia Commons, served
       * from our own origin so the sign-in page never depends on a third party
       * being up. The panel is the picture; nothing is written over it.
       */}
      <div className="relative hidden bg-[color:var(--ink)] lg:block">
        <img
          src="/images/uae-marina.jpg"
          alt=""
          aria-hidden
          className="absolute inset-0 size-full object-cover"
        />
      </div>

      <div className="flex items-center justify-center bg-hull px-4 py-12">
        <div className="w-full max-w-[380px]">
          <img
            src="/images/walidia-logo.png"
            alt="Walidia Yachts"
            className="mb-10 h-14 w-auto object-contain"
          />

          <h1 className="text-h1 text-ink">Sign in</h1>
          <p className="mt-2 text-body text-ink-soft">Use your Walidia account.</p>

          {status && (
            <p className="mt-4 rounded-card border border-success bg-success-bg px-3 py-2 text-small text-success">
              {status}
            </p>
          )}

          <form onSubmit={submit} className="mt-6 flex flex-col gap-3">
            <Input
              label="Email"
              type="email"
              name="email"
              autoComplete="username"
              required
              autoFocus
              value={data.email}
              error={errors.email}
              onChange={(event) => setData('email', event.target.value)}
            />

            <PasswordInput
              label="Password"
              name="password"
              autoComplete="current-password"
              required
              value={data.password}
              error={errors.password}
              onChange={(event) => setData('password', event.target.value)}
            />

            <div className="flex items-center justify-between gap-3 pt-1">
              <Checkbox
                label="Keep me signed in"
                name="remember"
                checked={data.remember}
                onChange={(event) => setData('remember', event.target.checked)}
              />
              {canResetPassword && (
                <a href="/forgot-password" className="text-small text-accent hover:underline">
                  Forgot password?
                </a>
              )}
            </div>

            <Button type="submit" variant="primary" size="lg" block loading={processing} className="mt-3">
              Sign in
            </Button>
          </form>

          <p className="mt-6 text-small text-ink-faint">
            Sessions end after 8 hours of inactivity. You can review and revoke active sessions from your profile.
          </p>
        </div>
      </div>
    </div>
  )
}

Login.layout = undefined
