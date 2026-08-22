import { motion, useScroll, useTransform } from 'framer-motion'
import type { MotionValue } from 'framer-motion'
import { useRef } from 'react'
import type { CSSProperties, ReactNode } from 'react'

interface AnimatedTextProps {
  text: string
  className?: string
  style?: CSSProperties
}

export default function AnimatedText({ text, className, style }: AnimatedTextProps) {
  const containerRef = useRef<HTMLParagraphElement>(null)
  const { scrollYProgress } = useScroll({
    target: containerRef,
    offset: ['start 0.8', 'end 0.2'],
  })

  const total = text.length
  const words = text.split(' ')

  const nodes: ReactNode[] = []
  let globalIndex = 0

  words.forEach((word, wi) => {
    const startIndex = globalIndex
    nodes.push(
      <span key={`w-${wi}`} style={{ display: 'inline-block', whiteSpace: 'nowrap' }}>
        {word.split('').map((char, ci) => (
          <Char key={ci} char={char} index={startIndex + ci} total={total} progress={scrollYProgress} />
        ))}
      </span>,
    )
    globalIndex += word.length + 1
    if (wi < words.length - 1) nodes.push(' ')
  })

  return (
    <p ref={containerRef} className={className} style={style}>
      {nodes}
    </p>
  )
}

function Char({
  char,
  index,
  total,
  progress,
}: {
  char: string
  index: number
  total: number
  progress: MotionValue<number>
}) {
  const start = index / total
  const end = start + 1 / total
  const opacity = useTransform(progress, [start, end], [0.2, 1])
  const y = useTransform(progress, [start, end], [6, 0])

  return (
    <span style={{ position: 'relative' }}>
      <span style={{ visibility: 'hidden' }}>{char}</span>
      <motion.span style={{ position: 'absolute', left: 0, top: 0, opacity, y }}>{char}</motion.span>
    </span>
  )
}
