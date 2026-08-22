import { motion } from 'framer-motion'

export default function LiveProjectButton() {
  return (
    <motion.button
      className="rounded-full border-2 border-[#D7E2EA] px-8 py-3 text-sm font-medium uppercase tracking-widest text-[#D7E2EA] sm:px-10 sm:py-3.5 sm:text-base"
      whileHover={{ scale: 1.05, backgroundColor: 'rgba(215, 226, 234, 0.1)' }}
      whileTap={{ scale: 0.96 }}
      transition={{ type: 'spring', stiffness: 400, damping: 20 }}
    >
      Live Project
    </motion.button>
  )
}
