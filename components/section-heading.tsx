type SectionHeadingProps = {
  eyebrow?: string;
  title: string;
  description?: string;
};

export function SectionHeading({ eyebrow, title, description }: SectionHeadingProps) {
  return (
    <div className="mx-auto max-w-3xl text-center">
      {eyebrow ? <p className="text-sm font-semibold uppercase tracking-wide text-beacon-teal">{eyebrow}</p> : null}
      <h2 className="mt-2 text-2xl font-semibold text-beacon-navy sm:text-3xl">{title}</h2>
      {description ? <p className="mt-3 text-sm leading-6 text-beacon-muted sm:text-base">{description}</p> : null}
    </div>
  );
}
