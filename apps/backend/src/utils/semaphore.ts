import { CaseFile, CaseState, TrafficLight } from '@prisma/client';
import dayjs from 'dayjs';

export type CaseDerived = {
  daysRemaining: number;
  trafficLight: TrafficLight;
  isOverdue: boolean;
  derivedState: CaseState;
};

export function deriveCaseState(caseFile: Pick<CaseFile, 'dueDate' | 'closedAt' | 'state'>, warningDays: number): CaseDerived {
  if (caseFile.closedAt || caseFile.state === CaseState.CERRADO) {
    return { daysRemaining: 0, trafficLight: TrafficLight.GRIS, isOverdue: false, derivedState: CaseState.CERRADO };
  }

  const daysRemaining = dayjs(caseFile.dueDate).startOf('day').diff(dayjs().startOf('day'), 'day');
  if (daysRemaining <= 0) {
    return { daysRemaining, trafficLight: TrafficLight.ROJO, isOverdue: true, derivedState: CaseState.VENCIDO };
  }
  if (daysRemaining <= warningDays) {
    return { daysRemaining, trafficLight: TrafficLight.AMARILLO, isOverdue: false, derivedState: CaseState.EN_RIESGO };
  }
  return { daysRemaining, trafficLight: TrafficLight.VERDE, isOverdue: false, derivedState: CaseState.ABIERTO };
}
